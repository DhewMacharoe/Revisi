import 'dart:convert';

import 'package:http/http.dart' as http;

const String baseUrl = 'https://delbites.d4trpl-itdel.id/api';

class KeranjangService {
  static Future<bool> addToCart({
    required int idPelanggan,
    required int idMenu,
    required String namaMenu,
    required String kategori,
    required int jumlah,
    required int harga, // Ubah dari String ke int
    String? suhu,
    String? catatan,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(
            'https://delbites.d4trpl-itdel.id/api/keranjang'), // Pastikan URL benar
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'id_pelanggan': idPelanggan,
          'id_menu': idMenu,
          'nama_menu': namaMenu,
          'kategori': kategori,
          'jumlah': jumlah,
          'harga': harga,
          'suhu': suhu,
          'catatan': catatan,
        }),
      );

      print('Response status: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode >= 200 && response.statusCode < 300) {
        final responseData = jsonDecode(response.body);
        return true;
      } else {
        throw Exception(
            'Failed to add to cart. Status: ${response.statusCode}');
      }
    } catch (e) {
      print('Error adding to cart: $e');
      throw Exception('Failed to add to cart: $e');
    }
  }

  static int _parseHarga(String harga) {
    try {
      final numericHarga = harga.replaceAll(RegExp(r'\D'), '');
      return int.parse(numericHarga);
    } catch (e) {
      print('Error parsing harga: $e');
      return 0;
    }
  }
}
