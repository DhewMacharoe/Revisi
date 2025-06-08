import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

// Fungsi helper untuk mendapatkan ID pelanggan dari SharedPreferences
Future<int?> getPelangganId() async {
  final prefs = await SharedPreferences.getInstance();
  return prefs.getInt('id_pelanggan'); // Pastikan key 'id_pelanggan' sudah benar
}

class RiwayatPesananPage extends StatefulWidget {
  const RiwayatPesananPage({Key? key}) : super(key: key);

  @override
  _RiwayatPesananPageState createState() => _RiwayatPesananPageState();
}

class _RiwayatPesananPageState extends State<RiwayatPesananPage> {
  // Timer untuk auto-update
  Timer? _timer;

  // State untuk data dan UI
  String selectedStatus = "menunggu"; // Status default saat halaman dibuka
  List<dynamic> orders = [];
  bool isLoading = true; // Set true di awal agar loading saat pertama kali buka
  int currentPage = 1;
  int totalPages = 1;
  bool hasNextPage = true;

  // State untuk rating
  Map<int, double> tempRatings = {};
  Map<int, bool> submittedRatings = {};

  // Konfigurasi status dan warna
  final List<String> statusList = [
    "menunggu",
    "diproses",
    "selesai",
    "dibatalkan"
  ];

  final Map<String, Color> statusColors = {
    "menunggu": Colors.amber.shade700,
    "diproses": Colors.blue.shade700,
    "selesai": Colors.green.shade700,
    "dibatalkan": Colors.red.shade700,
  };

  @override
  void initState() {
    super.initState();
    // 1. Memuat data pertama kali saat halaman dibuka
    fetchOrders();

    // 2. Memulai timer untuk auto-update setiap 10 detik
    _timer = Timer.periodic(const Duration(seconds: 10), (timer) {
      if (mounted) {
        print('Otomatis memperbarui riwayat pesanan...');
        fetchOrders();
      }
    });
  }

  @override
  void dispose() {
    // 3. Wajib membatalkan timer saat halaman ditutup untuk mencegah error
    _timer?.cancel();
    super.dispose();
  }

  // Fungsi untuk mengambil data pesanan dari API
  Future<void> fetchOrders() async {
    // Hanya tampilkan loading indicator besar jika belum ada data sama sekali
    if (orders.isEmpty) {
      setState(() => isLoading = true);
    }

    try {
      final pelangganId = await getPelangganId();
      if (pelangganId == null) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text(
                  'ID Pelanggan tidak ditemukan. Silakan login kembali.')),
        );
        return;
      }

      final uri = Uri.parse(
              'http://127.0.0.1:8000/api/pemesanan/pelanggan/$pelangganId')
          .replace(queryParameters: {
        'status': selectedStatus,
        'page': currentPage.toString(),
      });

      final response = await http.get(uri);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            // Perbarui data dari API
            orders = List<Map<String, dynamic>>.from(data['data'] ?? []);
            currentPage = data['current_page'] ?? 1;
            totalPages = data['last_page'] ?? 1;
            hasNextPage = currentPage < totalPages;
          });
        }
      } else {
        throw Exception('Gagal memuat pesanan. Status: ${response.statusCode}');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Terjadi kesalahan: ${e.toString()}')),
        );
      }
      print('Error details: $e');
    } finally {
      if (mounted) {
        setState(() => isLoading = false);
      }
    }
  }

  // Fungsi untuk mengirim rating
  Future<void> _submitRating(int detailId, double rating) async {
    try {
      final res = await http.put(
        Uri.parse('http://127.0.0.1:8000/api/detail-pemesanan/$detailId/rating'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'rating': rating}),
      );

      if (res.statusCode == 200) {
        setState(() {
          submittedRatings[detailId] = true;
          tempRatings.remove(detailId);
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Rating berhasil dikirim. Terima kasih!')),
        );
      } else {
        final error = jsonDecode(res.body);
        throw Exception(error['message'] ?? 'Gagal mengirim rating');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Riwayat Pesanan',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
        // Mengatur warna ikon di AppBar (termasuk panah kembali) menjadi putih
        iconTheme: const IconThemeData(
          color: Colors.white,
        ),
      ),
      body: Column(
        children: [
          _buildStatusFilter(),
          Expanded(
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : _buildOrderList(),
          ),
          if (!isLoading && orders.isNotEmpty) _buildPaginationControls(),
        ],
      ),
    );
  }

  // Widget untuk filter status pesanan
  Widget _buildStatusFilter() {
    return Container(
      height: 60,
      color: Colors.white,
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: statusList.length,
        itemBuilder: (context, index) {
          final status = statusList[index];
          return Padding(
            padding: EdgeInsets.only(left: index == 0 ? 12.0 : 4.0, right: index == statusList.length - 1 ? 12.0 : 4.0),
            child: ChoiceChip(
              label: Text(
                status.toUpperCase(),
                style: TextStyle(
                  color: selectedStatus == status
                      ? Colors.white
                      : statusColors[status],
                  fontWeight: FontWeight.bold,
                ),
              ),
              selected: selectedStatus == status,
              selectedColor: statusColors[status],
              backgroundColor: Colors.grey[200],
              onSelected: (selected) {
                if (selected) {
                  setState(() {
                    selectedStatus = status;
                    currentPage = 1; // Kembali ke halaman 1 setiap ganti filter
                    orders.clear(); // Kosongkan list agar loading indicator muncul
                  });
                  fetchOrders();
                }
              },
            ),
          );
        },
      ),
    );
  }

  // Widget untuk menampilkan daftar pesanan
  Widget _buildOrderList() {
    if (orders.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Text(
            'Tidak ada pesanan dengan status "$selectedStatus".',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 16, color: Colors.grey[600]),
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: fetchOrders, // Memungkinkan pull-to-refresh
      child: ListView.builder(
        padding: const EdgeInsets.all(8.0),
        itemCount: orders.length,
        itemBuilder: (context, index) {
          final order = orders[index];
          return _buildOrderCard(order);
        },
      ),
    );
  }

  // Widget untuk satu kartu pesanan
  Widget _buildOrderCard(Map<String, dynamic> order) {
    final details = order['detail_pemesanan'] as List<dynamic>? ?? [];
    final status = order['status'].toString();

     return Card(
    elevation: 3,
    margin: const EdgeInsets.symmetric(vertical: 8.0),
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    child: ExpansionTile(
      tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      leading: CircleAvatar(
        backgroundColor: statusColors[status] ?? Colors.grey,
        child: Icon(Icons.receipt_long, color: Colors.white),
      ),
      title: Text(
        'Pesanan #${order['id']}',
        style: const TextStyle(fontWeight: FontWeight.bold),
      ),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(height: 4),
          Text('Total: Rp ${order['total_harga']}'),
          const SizedBox(height: 4),
          Text(
            status.toUpperCase(),
            style: TextStyle(
              color: statusColors[status],
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Divider(),
                Text('Tanggal: ${order['waktu_pemesanan']}', style: TextStyle(color: Colors.grey.shade700)),
                Text('Metode: ${order['metode_pembayaran']}', style: TextStyle(color: Colors.grey.shade700)),
                const SizedBox(height: 16),
                const Text('Detail Item:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                ...details.map((detail) => _buildOrderDetailItem(detail)),
                if (status == 'selesai') ...[
                  const SizedBox(height: 16),
                  const Text('Beri Rating Anda:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  ...details.map((detail) => _buildRatingItem(detail)),
                ]
              ],
            ),
          ),
        ],
      ),
    );
  }

  // Widget untuk setiap item dalam detail pesanan
  Widget _buildOrderDetailItem(Map<String, dynamic> detail) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Expanded(
            flex: 4,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(detail['menu']?['nama_menu']?.toString() ?? 'Nama Menu Tidak Ada'),
                if (detail['suhu'] != null)
                  Text('Suhu: ${detail['suhu']}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
              ],
            ),
          ),
          Expanded(flex: 1, child: Text('x${detail['jumlah']}')),
          Expanded(
            flex: 2,
            child: Text('Rp ${detail['harga_satuan'] * detail['jumlah']}', textAlign: TextAlign.end),
          ),
        ],
      ),
    );
  }

  // Widget untuk setiap item rating
  Widget _buildRatingItem(Map<String, dynamic> detail) {
    final detailId = detail['id'];
    final namaMenu = detail['menu']?['nama_menu'] ?? 'Menu';
    final ratingValue = double.tryParse(detail['rating']?.toString() ?? '0.0') ?? 0.0;
    final alreadyRated = submittedRatings[detailId] ?? ratingValue > 0;

    return Padding(
      padding: const EdgeInsets.only(top: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Divider(),
          Text(namaMenu),
          const SizedBox(height: 4),
          Row(
            children: [
              RatingBar.builder(
                initialRating: tempRatings[detailId] ?? ratingValue,
                minRating: 1,
                direction: Axis.horizontal,
                itemCount: 5,
                itemSize: 24,
                ignoreGestures: alreadyRated,
                itemBuilder: (context, _) => const Icon(Icons.star, color: Colors.amber),
                onRatingUpdate: (rating) {
                  setState(() {
                    tempRatings[detailId] = rating;
                  });
                },
              ),
              const Spacer(),
              if (!alreadyRated && tempRatings.containsKey(detailId))
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  onPressed: () => _submitRating(detailId, tempRatings[detailId]!),
                  child: const Text('Kirim'),
                ),
            ],
          ),
        ],
      ),
    );
  }

  // Widget untuk kontrol paginasi (halaman)
  Widget _buildPaginationControls() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      color: Colors.white,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          IconButton(
            icon: const Icon(Icons.chevron_left),
            onPressed: currentPage > 1 ? () => _changePage(currentPage - 1) : null,
          ),
          Text('Halaman $currentPage dari $totalPages'),
          IconButton(
            icon: const Icon(Icons.chevron_right),
            onPressed: hasNextPage ? () => _changePage(currentPage + 1) : null,
          ),
        ],
      ),
    );
  }

  // Fungsi untuk berpindah halaman
  void _changePage(int newPage) {
    setState(() {
      currentPage = newPage;
    });
    fetchOrders();
  }
}