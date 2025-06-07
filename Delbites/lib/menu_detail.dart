import 'dart:convert';

import 'package:Delbites/login_page.dart';
import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000';

class SuhuSelector extends StatelessWidget {
  final String? selectedSuhu;
  final ValueChanged<String> onSelected;

  const SuhuSelector({
    required this.selectedSuhu,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Pilih Suhu:',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            ChoiceChip(
              label: const Text('Panas'),
              selected: selectedSuhu == 'panas',
              onSelected: (selected) => onSelected('panas'),
              selectedColor: const Color(0xFF4C53A5),
              labelStyle: TextStyle(
                  color: selectedSuhu == 'panas' ? Colors.white : Colors.black),
            ),
            ChoiceChip(
              label: const Text('Dingin'),
              selected: selectedSuhu == 'dingin',
              onSelected: (selected) => onSelected('dingin'),
              selectedColor: const Color(0xFF4C53A5),
              labelStyle: TextStyle(
                  color:
                      selectedSuhu == 'dingin' ? Colors.white : Colors.black),
            ),
          ],
        ),
      ],
    );
  }
}

class MenuDetail extends StatefulWidget {
  final int menuId;
  final String name;
  final String price;
  final String deskripsi;
  final String imageUrl;
  final String rating;
  final String kategori;

  const MenuDetail({
    required this.menuId,
    required this.name,
    required this.price,
    required this.deskripsi,
    required this.imageUrl,
    required this.rating,
    required this.kategori,
  });

  @override
  State<MenuDetail> createState() => _MenuDetailState();
}

class _MenuDetailState extends State<MenuDetail> {
  String? selectedSuhu;
  String? catatanTambahan;
  final currencyFormatter = NumberFormat.decimalPattern('id');

  double getFinalPrice() {
    return double.tryParse(widget.price) ?? 0.0;
  }

  Future<void> addToCart() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final emailPelanggan = prefs.getString('email_pelanggan');
      // Retrieve the customer ID
      final idPelanggan = prefs.getInt('id_pelanggan');

      if (emailPelanggan == null ||
          emailPelanggan.isEmpty ||
          idPelanggan == null) {
        // Check for idPelanggan too
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
                'Anda belum teridentifikasi. Silakan lengkapi data pelanggan.'),
            backgroundColor: Colors.orange,
          ),
        );
        final result = await Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const LoginPage()),
        );

        if (result == true && mounted) {
          addToCart();
        } else {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text(
                  'Penambahan ke keranjang dibatalkan karena data pelanggan belum lengkap.',
                ),
                backgroundColor: Colors.orange,
              ),
            );
          }
        }
        return;
      }

      final body = jsonEncode({
        // Send id_pelanggan as required by the API
        'id_pelanggan': idPelanggan
            .toString(), // Convert int to String if API expects string
        'id_menu': widget.menuId.toString(),
        'nama_menu': widget.name,
        'kategori': widget.kategori,
        'suhu': selectedSuhu,
        'jumlah': 1,
        'harga': getFinalPrice(),
        'catatan': catatanTambahan ?? '',
      });

      final response = await http.post(
        Uri.parse('$baseUrl/api/keranjang'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: body,
      );

      print('API Response Status Code: ${response.statusCode}');
      print('API Response Body: ${response.body}');

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
        String errorMessage =
            errorData['message'] ?? 'Gagal menambahkan ke keranjang';
        if (errorData['errors'] != null) {
          errorData['errors'].forEach((key, value) {
            errorMessage += '\n${value[0]}';
          });
        }
        throw Exception(errorMessage);
      }
    } catch (e) {
      print('Error in addToCart: $e');
      if (e.toString().toLowerCase().contains('pelanggan tidak ditemukan') ||
          e.toString().toLowerCase().contains('tidak terautentikasi')) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove('id_pelanggan');
        await prefs.remove('email_pelanggan');
        await prefs.remove('nama_pelanggan');
        await prefs.remove('telepon_pelanggan');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                  'Sesi Anda tidak valid. Silakan lengkapi data pelanggan kembali.'),
              backgroundColor: Colors.orange,
            ),
          );
        }
      } else if (mounted) {
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
    final catatanController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Catatan Tambahan'),
          content: TextField(
            controller: catatanController,
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
                setState(() => catatanTambahan = catatanController.text);
                Navigator.pop(context);
                addToCart();
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
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Pilih versi minuman terlebih dahulu!'),
                    ),
                  );
                  return;
                }
                _showCatatanDialog(context);
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
