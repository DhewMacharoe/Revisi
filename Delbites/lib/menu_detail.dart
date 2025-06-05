import 'dart:convert';

import 'package:Delbites/login_page.dart'; // Import halaman login
import 'package:Delbites/suhu_selector.dart';
import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://localhost';

class MenuDetail extends StatefulWidget {
  final String name;
  final String price;
  final String imageUrl;
  final int menuId;
  final String rating;
  final String kategori;
  final String deskripsi;

  const MenuDetail({
    required this.name,
    required this.price,
    required this.imageUrl,
    required this.menuId,
    required this.rating,
    required this.kategori,
    required this.deskripsi,
    Key? key,
  }) : super(key: key);

  @override
  State<MenuDetail> createState() => _MenuDetailState();
}

class _MenuDetailState extends State<MenuDetail> {
  String? selectedSuhu;
  String? catatanTambahan;
  final currencyFormatter = NumberFormat.decimalPattern('id');

  int getFinalPrice() {
    int basePrice = int.tryParse(widget.price) ?? 0;
    if (widget.kategori == 'minuman' && selectedSuhu == 'dingin') {
      return basePrice + 2000; // Tambah biaya untuk minuman dingin
    }
    return basePrice;
  }

  Future<void> addToCart() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final idPelanggan = prefs.getInt('id_pelanggan');

      if (idPelanggan == null || idPelanggan == 0) {
        // Cek juga jika idPelanggan adalah 0 (nilai fallback)
        // Jika idPelanggan null atau 0, arahkan ke halaman login
        final result = await Navigator.push(
          context,
          MaterialPageRoute(
              builder: (context) => const LoginPage()), // Arahkan ke LoginPage
        );

        // Jika data berhasil diisi (result adalah true), coba lagi tambahkan ke keranjang
        if (result == true) {
          addToCart();
        } else {
          // Jika pengguna membatalkan atau tidak mengisi data, tampilkan pesan
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text(
                    'Penambahan ke keranjang dibatalkan karena data pelanggan belum lengkap.'),
                backgroundColor: Colors.orange,
              ),
            );
          }
        }
        return;
      }

      final body = jsonEncode({
        'id_menu': widget.menuId,
        'id_pelanggan': idPelanggan,
        'nama_menu': widget.name,
        'kategori': widget.kategori,
        'suhu': selectedSuhu,
        'jumlah': 1,
        'harga': getFinalPrice(),
        'catatan': catatanTambahan ?? '',
      });

      final response = await http.post(
        Uri.parse('$baseUrl/api/keranjang'),
        headers: {'Content-Type': 'application/json'},
        body: body,
      );

      if (response.statusCode >= 200 && response.statusCode < 300) {
        final responseData = json.decode(response.body);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(responseData['message'] ??
                  'Item berhasil ditambahkan ke keranjang'),
              backgroundColor: Colors.green,
            ),
          );
        }
        Navigator.pop(context);
      } else {
        final errorData = json.decode(response.body);
        throw Exception(
            errorData['message'] ?? 'Gagal menambahkan ke keranjang');
      }
    } catch (e) {
      final errorMsg = e.toString();
      // Periksa apakah error terkait validasi atau pelanggan belum terdaftar
      if (errorMsg.toLowerCase().contains('validasi') ||
          errorMsg.toLowerCase().contains('pelanggan')) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove(
            'id_pelanggan'); // Hapus ID pelanggan lokal yang tidak valid
        // Tidak perlu memanggil _showPelangganDialog lagi, karena sudah ditangani di awal addToCart
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Gagal menambahkan ke keranjang: ${e.toString().contains("Exception:") ? e.toString().split("Exception:")[1].trim() : e.toString()}',
              style: const TextStyle(color: Colors.white),
            ),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _showCatatanDialog(BuildContext context) {
    final _catatanController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Catatan Tambahan'),
          content: TextField(
            controller: _catatanController,
            decoration: const InputDecoration(
              hintText: 'Contoh: Kurangi gula, tanpa es...',
            ),
            maxLines: 3,
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () {
                setState(() => catatanTambahan = _catatanController.text);
                Navigator.pop(context);
                addToCart(); // Panggil addToCart setelah catatan diisi
              },
              child: const Text('Tambah'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final double initialRating = double.tryParse(widget.rating) ?? 0.0;

    return Scaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF2D5EA2),
        title: Text(widget.name,
            style: const TextStyle(
                color: Colors.white, fontWeight: FontWeight.bold)),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(15),
                child: Image.network(
                  widget.imageUrl.isNotEmpty
                      ? widget.imageUrl
                      : 'https://via.placeholder.com/200',
                  height: 200,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  loadingBuilder: (context, child, loadingProgress) =>
                      loadingProgress == null
                          ? child
                          : const Center(child: CircularProgressIndicator()),
                  errorBuilder: (context, error, stackTrace) => Container(
                    height: 200,
                    color: Colors.grey[300],
                    child: const Center(child: Icon(Icons.fastfood, size: 80)),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 20),
            Text(widget.name,
                style:
                    const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
            const SizedBox(height: 10),
            Text('Rp${currencyFormatter.format(getFinalPrice())}',
                style: const TextStyle(fontSize: 18, color: Colors.grey)),
            const SizedBox(height: 10),
            const Text('Deskripsi:',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 5),
            Text(widget.deskripsi, style: const TextStyle(fontSize: 16)),
            const SizedBox(height: 20),
            if (widget.kategori == 'minuman')
              SuhuSelector(
                selectedSuhu: selectedSuhu,
                onSelected: (suhu) => setState(() => selectedSuhu = suhu),
              ),
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(Icons.star, color: Colors.amber, size: 20),
                const SizedBox(width: 5),
                Text('${initialRating.toStringAsFixed(1)} / 5.0',
                    style: const TextStyle(fontSize: 16)),
              ],
            ),
            RatingBarIndicator(
              rating: initialRating,
              itemBuilder: (context, index) =>
                  const Icon(Icons.star, color: Colors.amber),
              itemCount: 5,
              itemSize: 30.0,
              direction: Axis.horizontal,
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              key: const Key("add-to-cart-button"),
              onPressed: () {
                if (widget.kategori == 'minuman' && selectedSuhu == null) {
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Pilih versi minuman terlebih dahulu!'),
                      ),
                    );
                  }
                  return;
                }
                _showCatatanDialog(
                    context); // Panggil dialog catatan terlebih dahulu
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4C53A5),
                padding: const EdgeInsets.symmetric(vertical: 15),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
              ),
              child: const Center(
                child: Text("Tambah ke Keranjang",
                    style: TextStyle(color: Colors.white, fontSize: 16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
