import 'dart:convert';

import 'package:Delbites/checkout.dart';
import 'package:Delbites/main_screen.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class KeranjangPage extends StatefulWidget {
  final int idPelanggan;

  const KeranjangPage({Key? key, required this.idPelanggan}) : super(key: key);

  @override
  State<KeranjangPage> createState() => _KeranjangPageState();
}

class _KeranjangPageState extends State<KeranjangPage> {
  List<Map<String, dynamic>> pesanan = [];
  bool isLoading = true;
  bool isError = false;
  List<TextEditingController> _quantityControllers = [];
  List<FocusNode> _focusNodes = [];

  @override
  void initState() {
    super.initState();
    _loadKeranjang();
  }

  @override
  void dispose() {
    for (var controller in _quantityControllers) {
      controller.dispose();
    }
    for (var node in _focusNodes) {
      node.dispose();
    }
    super.dispose();
  }

  Future<void> _loadKeranjang() async {
    try {
      final response = await http.get(Uri.parse(
          'http://localhost/api/keranjang/pelanggan/${widget.idPelanggan}'));

      if (response.statusCode == 200) {
        List<dynamic> data = json.decode(response.body);
        setState(() {
          pesanan = data.map<Map<String, dynamic>>((item) {
            final menu = item['menu'] ?? {};
            double harga = double.tryParse(item['harga'].toString()) ?? 0;
            return {
              'id': item['id'],
              'id_menu': item['id_menu'],
              'name': item['nama_menu'],
              'price': harga.toInt(),
              'quantity': item['jumlah'],
              'catatan': item['catatan'],
              'suhu': item['suhu'],
              'image': menu['gambar'],
            };
          }).toList();

          _quantityControllers = pesanan
              .map((item) =>
                  TextEditingController(text: item['quantity'].toString()))
              .toList();

          _focusNodes = List.generate(pesanan.length, (index) => FocusNode());
          for (int i = 0; i < _focusNodes.length; i++) {
            _focusNodes[i].addListener(() {
              if (!_focusNodes[i].hasFocus) {
                final value = _quantityControllers[i].text;
                final parsed = int.tryParse(value);
                if (parsed == null || parsed < 1) {
                  _quantityControllers[i].text = '1';
                  _updateQuantity(i, 1);
                }
              }
            });
          }

          isLoading = false;
        });
      } else {
        throw Exception('Gagal memuat keranjang');
      }
    } catch (e) {
      setState(() {
        isError = true;
        isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memuat keranjang: $e')),
      );
    }
  }

  Future<void> _updateQuantity(int index, int newQuantity) async {
    if (newQuantity < 1) return;

    try {
      final item = pesanan[index];

      final response = await http.put(
        Uri.parse('http://localhost/api/keranjang/${item['id']}'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'jumlah': newQuantity}),
      );

      if (response.statusCode >= 200 && response.statusCode < 300) {
        setState(() {
          pesanan[index]['quantity'] = newQuantity;
          _quantityControllers[index].text = newQuantity.toString();
        });
      } else {
        throw Exception('Gagal mengupdate jumlah');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mengupdate jumlah: $e')),
      );
    }
  }

  Future<void> _removeItem(int index) async {
    try {
      final item = pesanan[index];
      final response = await http.delete(
        Uri.parse('http://localhost/api/keranjang/${item['id']}'),
      );

      if (response.statusCode >= 200 && response.statusCode < 300) {
        setState(() {
          pesanan.removeAt(index);
          _quantityControllers.removeAt(index);
        });
      } else {
        throw Exception('Gagal menghapus item');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal menghapus item: $e')),
      );
    }
  }

  int getTotalHarga() {
    int total = 0;
    for (var item in pesanan) {
      int price = item['price'] ?? 0;
      int quantity = item['quantity'] ?? 0;
      total += price * quantity;
    }
    return total;
  }

  String formatPrice(int price) {
    return price.toString().replaceAllMapped(
          RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
          (m) => '${m[1]}.',
        );
  }

  Widget _buildCartContent() {
    if (isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (isError) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('Gagal memuat keranjang',
                style: TextStyle(fontSize: 18)),
            const SizedBox(height: 10),
            ElevatedButton(
              onPressed: _loadKeranjang,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (pesanan.isEmpty) {
      return const Center(
        child: Text(
          'Keranjang masih kosong',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.only(bottom: 80),
      itemCount: pesanan.length,
      itemBuilder: (context, index) {
        final item = pesanan[index];

        return Dismissible(
          key: Key(item['id'].toString()),
          direction: DismissDirection.endToStart,
          onDismissed: (_) => _removeItem(index),
          background: Container(
            alignment: Alignment.centerRight,
            padding: const EdgeInsets.only(right: 20),
            color: Colors.red,
            child: const Icon(Icons.delete, color: Colors.white),
          ),
          child: buildCartItem(item, index),
        );
      },
    );
  }

  Widget buildCartItem(Map<String, dynamic> item, int index) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                item['image'] != null
                    ? Image.network(
                        'http://localhost/storage/${item['image']}',
                        width: 40,
                        height: 40,
                        fit: BoxFit.cover,
                      )
                    : const Icon(Icons.fastfood, size: 40),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item['name'],
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      Text(
                        'Rp${formatPrice(item['price'])}',
                        style: const TextStyle(color: Colors.grey),
                      ),
                      if (item['suhu'] != null && item['suhu'].isNotEmpty)
                        Text(
                          'Suhu: ${item['suhu']}',
                          style: const TextStyle(color: Colors.grey),
                        ),
                    ],
                  ),
                ),
                Row(
                  children: [
                    IconButton(
                      icon:
                          const Icon(Icons.remove_circle, color: Colors.black),
                      onPressed: () {
                        final updated = item['quantity'] - 1;
                        _quantityControllers[index].text = updated.toString();
                        _updateQuantity(index, updated);
                      },
                    ),
                    SizedBox(
                      width: 40,
                      child: TextFormField(
                        controller: _quantityControllers[index],
                        textAlign: TextAlign.center,
                        keyboardType: TextInputType.number,
                        onFieldSubmitted: (value) {
                          final parsed = int.tryParse(value);
                          if (parsed != null && parsed > 0) {
                            _updateQuantity(index, parsed);
                          } else {
                            _quantityControllers[index].text = '1';
                            _updateQuantity(index, 1);
                          }
                        },
                        decoration: const InputDecoration(
                          isDense: true,
                          contentPadding: EdgeInsets.symmetric(vertical: 4),
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.add_circle, color: Colors.black),
                      onPressed: () {
                        final updated = item['quantity'] + 1;
                        _quantityControllers[index].text = updated.toString();
                        _updateQuantity(index, updated);
                      },
                    ),
                  ],
                ),
              ],
            ),
            if (item['catatan'] != null && item['catatan'].isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 8.0),
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
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Pesanan',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
        leading: BackButton(
          onPressed: () => Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (_) => const MainScreen()),
          ),
        ),
      ),
      body: _buildCartContent(),
      floatingActionButton: pesanan.isNotEmpty
          ? Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2D5EA2),
                  minimumSize: const Size.fromHeight(50),
                ),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => CheckoutPage(
                        pesanan: pesanan,
                        idPelanggan: widget.idPelanggan,
                        totalHarga: getTotalHarga(),
                      ),
                    ),
                  ).then((_) => _loadKeranjang());
                },
                child: Text(
                  'Rp${formatPrice(getTotalHarga())} - Checkout',
                  style: const TextStyle(
                      color: Colors.white, fontWeight: FontWeight.bold),
                ),
              ),
            )
          : null,
      floatingActionButtonLocation: FloatingActionButtonLocation.centerFloat,
    );
  }
}
