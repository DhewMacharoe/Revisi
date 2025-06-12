import 'dart:convert';
import 'dart:io';

import 'package:Delbites/midtrans_payment_page.dart';
import 'package:Delbites/utils/payment_utils.dart';
import 'package:Delbites/waiting_page.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000';

class CheckoutPage extends StatefulWidget {
  final List<Map<String, dynamic>> pesanan;
  final int idPelanggan;
  final int totalHarga;

  const CheckoutPage({
    Key? key,
    required this.pesanan,
    required this.idPelanggan,
    required this.totalHarga,
  }) : super(key: key);

  @override
  _CheckoutPageState createState() => _CheckoutPageState();
}

class _CheckoutPageState extends State<CheckoutPage> {
  String? selectedPayment;
  bool isLoading = true; // Set true di awal untuk loading status
  bool isTokoBuka = true; // Default true, akan diupdate oleh API

  final TextEditingController nameController = TextEditingController();
  final TextEditingController emailController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();

  @override
  void initState() {
    super.initState();
    // Panggil kedua fungsi secara bersamaan untuk efisiensi
    _initializePage();
  }

  Future<void> _initializePage() async {
    // Jalankan pengecekan status dan pengambilan data pelanggan
    await Future.wait([
      checkOperationalStatus(),
      _getCustomerName(),
    ]);

    // Setelah selesai, matikan loading
    if (mounted) {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _getCustomerName() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        emailController.text = prefs.getString('email_pelanggan') ?? '';
        nameController.text = prefs.getString('nama_pelanggan') ?? '';
        phoneController.text = prefs.getString('telepon_pelanggan') ?? '';
      });
    }
  }

  Future<void> checkOperationalStatus() async {
    try {
      final response =
          await http.get(Uri.parse('$baseUrl/api/operasional/status'));
      if (response.statusCode == 200 && mounted) {
        final data = jsonDecode(response.body);
        setState(() {
          isTokoBuka = data['status'] == 'buka';
        });
        if (!isTokoBuka) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(data['message'] ?? 'Maaf, toko sedang tutup.'),
              behavior: SnackBarBehavior.floating,
              margin: const EdgeInsets.all(20),
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        // Jika gagal konek API, anggap tutup untuk keamanan
        setState(() {
          isTokoBuka = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text(
                  'Gagal terhubung ke server. Tidak dapat melakukan pemesanan.')),
        );
      }
    }
  }

  bool isValidEmail(String email) {
    final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    return emailRegex.hasMatch(email);
  }

  int getTotalHarga() {
    // Logika ini bisa disederhanakan karena totalHarga sudah di-pass dari widget
    return widget.totalHarga;
  }

Future<void> processPayment() async {
  // Proses pembayaran non-tunai (Midtrans)
  final nama = nameController.text.trim();
  final telepon = phoneController.text.trim();
  final email = emailController.text.trim();

  if (nama.isEmpty || telepon.isEmpty || email.isEmpty) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Lengkapi semua data pelanggan.')),
    );
    return;
  }

  if (!isValidEmail(email)) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Format email tidak valid.')),
    );
    return;
  }

  setState(() => isLoading = true);

  try {
    final prefs = await SharedPreferences.getInstance();
    final existingOrderId = prefs.getString('midtrans_order_id');
    final existingRedirectUrl = prefs.getString('midtrans_redirect_url');

    if (existingOrderId != null && existingRedirectUrl != null) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => MidtransPaymentPage(
            redirectUrl: existingRedirectUrl,
            orderId: existingOrderId,
            pesanan: widget.pesanan,
            idPelanggan: widget.idPelanggan,
            totalHarga: getTotalHarga(),
          ),
        ),
      );
      // Jangan lupa set isLoading ke false jika hanya redirect
      setState(() => isLoading = false); 
      return;
    }

    await prefs.remove('midtrans_order_id');
    await prefs.remove('midtrans_redirect_url');

    final int idPelanggan =
        await getOrCreatePelangganId(nama, telepon, email);
    final grossAmount = getTotalHarga();

    final pemesananResponse = await http.post(
      Uri.parse('$baseUrl/api/pemesanan'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'id_pelanggan': idPelanggan,
        'admin_id': null,
        'total_harga': grossAmount,
        'metode_pembayaran': 'transfer bank', // atau 'non-tunai'
        'status': 'pembayaran',
        'waktu_pengambilan': DateTime.now().toIso8601String(),
        'detail_pemesanan': buildDetailPemesanan(widget.pesanan),
      }),
    );

    if (pemesananResponse.statusCode != 201) {
      throw Exception('Gagal menyimpan pesanan: ${pemesananResponse.body}');
    }

    // Setelah pesanan berhasil disimpan, hapus keranjang belanja.
    // Sama seperti di processCashPayment()
    await http.delete(
      Uri.parse('$baseUrl/api/keranjang/pelanggan/$idPelanggan'),
      headers: {'Content-Type': 'application/json'},
    );

    final pemesananData = jsonDecode(pemesananResponse.body);
    final int idPemesanan = pemesananData['data']['id'];
    final orderId =
        'ORDER-$idPemesanan-${DateTime.now().millisecondsSinceEpoch}';

    final items = widget.pesanan
        .map((item) => {
              'id': item['id_menu'].toString(),
              'name': item['name'],
              'price': item['price'],
              'quantity': item['quantity'],
            })
        .toList();

    final response = await http.post(
      Uri.parse('$baseUrl/api/midtrans/create-transaction'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'id_pelanggan': idPelanggan,
        'order_id': orderId,
        'gross_amount': grossAmount,
        'first_name': nama,
        'last_name': '(Del)',
        'email': email,
        'items': items,
      }),
    );

    if (response.headers['content-type']?.contains('application/json') ==
        true) {
      final result = jsonDecode(response.body);

      if (response.statusCode == 200) {
        final String redirectUrl = result['redirect_url'] ?? '';
        final String orderIdFromMidtrans = result['order_id'] ?? '';

        await prefs.setString('midtrans_order_id', orderIdFromMidtrans);
        await prefs.setString('midtrans_redirect_url', redirectUrl);

        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => MidtransPaymentPage(
              redirectUrl: redirectUrl,
              orderId: orderIdFromMidtrans,
              pesanan: widget.pesanan,
              idPelanggan: idPelanggan,
              totalHarga: grossAmount,
            ),
          ),
        );
        // Tidak perlu return di sini, biarkan finally yang handle isLoading
      } else {
        throw Exception('Gagal membuat transaksi: ${result['message']}');
      }
    } else {
      print("Response bukan JSON:\n${response.body}");
      throw Exception('Server mengembalikan format tidak dikenali.');
    }
  } catch (e) {
    print("Payment error: $e");
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Payment error: $e')),
    );
  } finally {
    // Pastikan loading berhenti meskipun ada redirect atau error
    if(mounted) {
      setState(() => isLoading = false);
    }
  }
}

  Future<void> processCashPayment() async {
    // Proses pembayaran tunai
    // (Kode processCashPayment Anda tidak perlu diubah)
    setState(() => isLoading = true);
    try {
      final nama = nameController.text.trim();
      final telepon = phoneController.text.trim();
      final email = emailController.text.trim();

      if (nama.isEmpty || telepon.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Nama dan nomor WhatsApp wajib diisi.')),
        );
        setState(() => isLoading = false);
        return;
      }

      final idPelanggan = await getOrCreatePelangganId(nama, telepon, email);

      final response = await http.post(
        Uri.parse('$baseUrl/api/pemesanan'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'id_pelanggan': idPelanggan,
          'admin_id': null,
          'total_harga': getTotalHarga(),
          'metode_pembayaran': 'tunai',
          'status': 'menunggu',
          'waktu_pengambilan': DateTime.now().toIso8601String(),
          'detail_pemesanan': buildDetailPemesanan(widget.pesanan),
        }),
      );

      if (response.statusCode == 201) {
        await http.delete(
          Uri.parse('$baseUrl/api/keranjang/pelanggan/$idPelanggan'),
          headers: {'Content-Type': 'application/json'},
        );

        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => WaitingPage(orders: widget.pesanan),
          ),
        );
      } else {
        throw Exception('Gagal menyimpan pesanan: ${response.body}');
      }
    } catch (e) {
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text('Gagal bayar: $e')));
    } finally {
      setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        _showCancelDialog(context);
        return false;
      },
      child: Scaffold(
        appBar: AppBar(
          title: const Text(
            'Bayar',
            style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
          ),
          backgroundColor: const Color(0xFF2D5EA2),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.white),
            onPressed: () => _showCancelDialog(context),
          ),
        ),
        body: isLoading
            ? const Center(child: CircularProgressIndicator())
            : SingleChildScrollView(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Pembayaran',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 16),
                    if (!isTokoBuka)
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.red.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.info_outline, color: Colors.red[800]),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                'Toko sedang tutup. Pemesanan tidak dapat dilakukan saat ini.',
                                style: TextStyle(
                                    color: Colors.red[800],
                                    fontWeight: FontWeight.w600),
                              ),
                            ),
                          ],
                        ),
                      ),
                    const SizedBox(height: 16),
                    SizedBox(
                      height: 100,
                      child: ListView(
                        scrollDirection: Axis.horizontal,
                        children: [
                          _paymentOption(
                              'Bayar langsung di kasir', Icons.store),
                          _paymentOption('Bayar Non-Tunai', Icons.credit_card),
                        ],
                      ),
                    ),
                    if (selectedPayment != null &&
                        selectedPayment != 'Bayar langsung di kasir')
                      // Widget untuk informasi customer (tetap sama)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Customer Information',
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 20),
                            TextField(
                              controller: phoneController,
                              decoration: const InputDecoration(
                                labelText: 'Nomor WhatsApp',
                                border: OutlineInputBorder(),
                              ),
                              readOnly: true,
                              keyboardType: TextInputType.phone,
                            ),
                            const SizedBox(height: 10),
                            TextField(
                              controller: nameController,
                              decoration: const InputDecoration(
                                labelText: 'Full Name',
                                border: OutlineInputBorder(),
                              ),
                              readOnly: true,
                            ),
                            const SizedBox(height: 20),
                            TextField(
                              controller: emailController,
                              decoration: const InputDecoration(
                                labelText: 'Email',
                                border: OutlineInputBorder(),
                              ),
                              keyboardType: TextInputType.emailAddress,
                              readOnly: true,
                            ),
                          ],
                        ),
                      ),
                    const SizedBox(height: 30),
                    const Text(
                      'Pesanan',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 10),
                    ...widget.pesanan.map((item) {
                      return Card(
                        child: Padding(
                          padding: const EdgeInsets.all(8.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              ListTile(
                                leading: item['image'] != null
                                    ? Image.network(
                                        'http://127.0.0.1:8000/storage/${item['image']}',
                                        width: 40,
                                        height: 40,
                                        fit: BoxFit.cover,
                                      )
                                    : const Icon(Icons.fastfood, size: 40),
                                title: Text(item['name']),
                                subtitle: Text(
                                  '${PaymentUtils.formatToRupiah(item['price'])} x ${item['quantity']}',
                                ),
                              ),
                              if (item['suhu'] != null)
                                Padding(
                                  padding: const EdgeInsets.only(
                                      left: 16.0, bottom: 8.0),
                                  child: Text('Suhu: ${item['suhu']}',
                                      style:
                                          const TextStyle(color: Colors.grey)),
                                ),
                              if (item['catatan'] != null &&
                                  item['catatan'].isNotEmpty)
                                Padding(
                                  padding: const EdgeInsets.only(
                                      left: 16.0, bottom: 8.0),
                                  child: Text(
                                    'Catatan: ${item['catatan']}',
                                    style: const TextStyle(
                                      fontStyle: FontStyle.italic,
                                      color: Colors.grey,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                      );
                    }).toList(),
                    const SizedBox(height: 80),
                  ],
                ),
              ),
        bottomNavigationBar: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          decoration: const BoxDecoration(
            color: Colors.white,
            boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 5)],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Total Harga:',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  Text(
                    PaymentUtils.formatToRupiah(getTotalHarga()),
                    style: const TextStyle(
                        fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: isTokoBuka && selectedPayment != null
                      ? (selectedPayment == 'Bayar langsung di kasir'
                          ? processCashPayment
                          : processPayment)
                      : null,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isTokoBuka && selectedPayment != null
                        ? const Color(0xFF2D5EA2)
                        : Colors.grey,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8)),
                  ),
                  child: Text(
                    !isTokoBuka
                        ? 'Toko Sedang Tutup'
                        : (selectedPayment == null
                            ? 'Pilih Pembayaran'
                            : 'Bayar dengan $selectedPayment'),
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _paymentOption(String title, IconData icon) {
    final bool isEnabled = isTokoBuka;

    return GestureDetector(
      onTap: isEnabled
          ? () {
              setState(() {
                selectedPayment = title;
              });
            }
          : null,
      child: Opacity(
        opacity: isEnabled ? 1.0 : 0.5,
        child: Padding(
          padding: const EdgeInsets.only(right: 10),
          child: Container(
            width: 120,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: selectedPayment == title && isEnabled
                    ? const Color(0xFF2D5EA2)
                    : Colors.grey,
                width: selectedPayment == title && isEnabled ? 2 : 1,
              ),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(icon,
                    size: 30,
                    color: isEnabled ? const Color(0xFF2D5EA2) : Colors.grey),
                const SizedBox(height: 5),
                Text(
                  title,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                      fontSize: 12,
                      color: isEnabled ? Colors.black87 : Colors.grey[600]),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showCancelDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text("Batal Checkout"),
          content:
              const Text("Apakah anda yakin ingin membatalkan pembayaran?"),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text("Tidak"),
            ),
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                Navigator.of(context).pop();
              },
              child: const Text("Ya"),
            ),
          ],
        );
      },
    );
  }
}

// Helper functions (tidak perlu diubah)
List<Map<String, dynamic>> buildDetailPemesanan(
    List<Map<String, dynamic>> pesanan) {
  return pesanan.map((item) {
    final int harga = item['price'] is int
        ? item['price']
        : int.tryParse(item['price']
                .toString()
                .replaceAll('Rp ', '')
                .replaceAll('.', '')) ??
            0;

    final int jumlah = item['quantity'] is int
        ? item['quantity']
        : int.tryParse(item['quantity'].toString()) ?? 0;

    return {
      'id_menu': item['id_menu'],
      'jumlah': jumlah,
      'harga_satuan': harga,
      'subtotal': harga * jumlah,
      'suhu': item['suhu'] ?? '',
      'catatan': item['catatan'] ?? '',
    };
  }).toList();
}

Future<String> getDeviceId() async {
  final deviceInfo = DeviceInfoPlugin();
  if (Platform.isAndroid) {
    final info = await deviceInfo.androidInfo;
    return info.id;
  } else if (Platform.isIOS) {
    final info = await deviceInfo.iosInfo;
    return info.identifierForVendor ?? '';
  }
  return '';
}

Future<int> getOrCreatePelangganId(
    String nama, String telepon, String email) async {
  final prefs = await SharedPreferences.getInstance();
  final existingId = prefs.getInt('id_pelanggan');
  if (existingId != null) return existingId;

  final deviceId = await getDeviceId();
  if (!prefs.containsKey('device_id')) {
    await prefs.setString('device_id', deviceId);
  }

  final checkResponse = await http.get(
    Uri.parse('$baseUrl/api/pelanggan/by-device?device_id=$deviceId'),
  );

  if (checkResponse.statusCode == 200) {
    final data = jsonDecode(checkResponse.body);
    await prefs.setInt('id_pelanggan', data['id']);
    await prefs.setString('nama_pelanggan', data['nama']);
    await prefs.setString('telepon_pelanggan', data['telepon']);
    await prefs.setString('email_pelanggan', data['email']);
    return data['id'];
  }

  final createResponse = await http.post(
    Uri.parse('$baseUrl/api/pelanggan'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'nama': nama,
      'telepon': telepon,
      'email': email,
      'device_id': deviceId,
    }),
  );

  final created = jsonDecode(createResponse.body);
  final id = created['data']['id'];
  await prefs.setInt('id_pelanggan', id);
  await prefs.setString('nama_pelanggan', nama);
  await prefs.setString('telepon_pelanggan', telepon);
  return id;
}
