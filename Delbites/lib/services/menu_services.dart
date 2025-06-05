import 'dart:convert';
import 'package:http/http.dart' as http;

const String baseUrl = 'https://delbites.d4trpl-itdel.id';

class MenuService {
  static Future<List<Map<String, String>>> fetchMenu() async {
    final response = await http.get(Uri.parse('$baseUrl/api/menu'));

    if (response.statusCode == 200) {
      final List<dynamic> data = jsonDecode(response.body);
      return data.map<Map<String, String>>((item) {
        return {
          'id': item['id'].toString(),
          'name': item['nama_menu'].toString(),
          'price': item['harga'].toString(),
          'stok': item['stok'].toString(),
          'stok_terjual': (item['total_terjual'] ?? '0').toString(),
          'kategori': item['kategori'].toString(),
          'image': item['gambar'].toString(),
          'rating': (item['rating'] ?? '0.0').toString(),
          'deskripsi': item['deskripsi']?.toString() ?? '',
        };
      }).toList();
    } else {
      throw Exception('Gagal memuat menu');
    }
  }

  
}
