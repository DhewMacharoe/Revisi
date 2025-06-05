import 'dart:convert';

import 'package:http/http.dart' as http;

class DetailPemesananService {
  final String baseUrl = 'http://localhost/api/detail-pemesanan';

  Future<List<dynamic>> fetchDetailPemesanan() async {
    final response = await http.get(Uri.parse(baseUrl));
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to load detail pemesanan');
    }
  }

  Future<void> addDetailPemesanan(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse(baseUrl),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(data),
    );
    if (response.statusCode != 200 && response.statusCode != 201) {
      throw Exception('Failed to add detail pemesanan');
    }
  }

  Future<void> updateDetailPemesanan(int id, Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/$id'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(data),
    );
    if (response.statusCode != 200) {
      throw Exception('Failed to update detail pemesanan');
    }
  }

  Future<void> deleteDetailPemesanan(int id) async {
    final response = await http.delete(Uri.parse('$baseUrl/$id'));
    if (response.statusCode != 200) {
      throw Exception('Failed to delete detail pemesanan');
    }
  }
}
