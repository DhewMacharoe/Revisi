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

  // Constructor sudah disesuaikan untuk Flutter versi lama
  const SuhuSelector({
    Key? key,
    required this.selectedSuhu,
    required this.onSelected,
  }) : super(key: key);

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

  // Constructor sudah disesuaikan untuk Flutter versi lama
  const MenuDetail({
    Key? key,
    required this.menuId,
    required this.name,
    required this.price,
    required this.deskripsi,
    required this.imageUrl,
    required this.rating,
    required this.kategori,
  }) : super(key: key);

  @override
  State<MenuDetail> createState() => _MenuDetailState();
}

class _MenuDetailState extends State<MenuDetail> {
  String? selectedSuhu;
  final currencyFormatter = NumberFormat.decimalPattern('id');

  // Modified getBasePrice to reflect the temperature selection
  double getMenuPrice() {
    double basePrice = double.tryParse(widget.price) ?? 0.0;
    if (widget.kategori == 'minuman' && selectedSuhu == 'dingin') {
      return basePrice + 2000; // Add 2000 for cold drinks
    }
    return basePrice;
  }

  Future<void> addToCart(int quantity, String? catatan) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final idPelanggan = prefs.getInt('id_pelanggan');

      if (idPelanggan == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Anda belum login. Silakan login terlebih dahulu.'),
            backgroundColor: Colors.orange,
          ),
        );
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const LoginPage()),
        );
        return;
      }

      final body = jsonEncode({
        'id_pelanggan': idPelanggan.toString(),
        'id_menu': widget.menuId.toString(),
        'nama_menu': widget.name,
        'kategori': widget.kategori,
        'suhu': selectedSuhu,
        'jumlah': quantity,
        'harga': getMenuPrice(), // Use getMenuPrice here
        'catatan': catatan ?? '',
      });

      final response = await http.post(
        Uri.parse('$baseUrl/api/keranjang'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
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
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Gagal: ${e.toString().contains("Exception:") ? e.toString().split("Exception:")[1].trim() : e.toString()}',
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
    int quantity = 1;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setStateInDialog) {
            void increment() {
              setStateInDialog(() => quantity++);
            }

            void decrement() {
              if (quantity > 1) {
                setStateInDialog(() => quantity--);
              }
            }

            return AlertDialog(
              title: const Text('Detail Pesananmu'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Jumlah Pesanan:',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        IconButton(
                          icon: const Icon(
                            Icons.remove_circle,
                            size: 32,
                          ),
                          onPressed: decrement,
                        ),
                        const SizedBox(width: 16),
                        Text(
                          '$quantity',
                          style: const TextStyle(
                              fontSize: 24, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(width: 16),
                        IconButton(
                          icon: const Icon(
                            Icons.add_circle,
                            size: 32,
                          ),
                          onPressed: increment,
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    const Text('Catatan Tambahan (Opsional):',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: catatanController,
                      decoration: const InputDecoration(
                        hintText: 'Contoh: Kurangi gula, tanpa es...',
                        border: OutlineInputBorder(),
                      ),
                      maxLines: 3,
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  onPressed: () {
                    final catatan = catatanController.text;
                    Navigator.pop(context);
                    addToCart(quantity, catatan);
                  },
                  child: const Text('Tambah'),
                ),
              ],
            );
          },
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
            // Display the dynamically calculated price
            Text('Rp${currencyFormatter.format(getMenuPrice())}',
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
                onSelected: (suhu) {
                  setState(() {
                    selectedSuhu = suhu;
                  });
                },
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
                      content: Text('Pilih suhu minuman terlebih dahulu!'),
                      backgroundColor: Colors.orange,
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
