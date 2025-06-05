import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class PelangganService {
  final String _baseUrl = 'http://localhost/api/pelanggan';

  Future<List<dynamic>> fetchPelanggan() async {
    final response = await http.get(Uri.parse('$_baseUrl/pelanggan'));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Gagal mengambil data pelanggan');
    }
  }

  Future<Map<String, dynamic>> getPelangganById(int id) async {
    final response = await http.get(Uri.parse('$_baseUrl/pelanggan/$id'));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Pelanggan tidak ditemukan');
    }
  }

  Future<Map<String, dynamic>> createPelanggan({
    required String nama,
    required String telepon,
    required String deviceId,
  }) async {
    final url = Uri.parse('http://localhost/api/pelanggan');

    final response = await http.post(
      url,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'nama': nama,
        'telepon': telepon,
        'device_id': deviceId,
      }),
    );

    if (response.statusCode == 201 || response.statusCode == 200) {
      final data = jsonDecode(response.body);

      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt('id_pelanggan', data['id']);
      await prefs.setString('nama_pelanggan', data['nama']);
      await prefs.setString('telepon_pelanggan', data['telepon'] ?? '');

      return data;
    } else {
      // Lempar error agar bisa ditangani di UI
      throw Exception('Gagal membuat pelanggan: ${response.body}');
    }
  }

  Future<Map<String, dynamic>> updatePelanggan({
    required int id,
    String? nama,
    String? telepon,
    String? password,
    String? status,
  }) async {
    final response = await http.put(
      Uri.parse('$_baseUrl/pelanggan/$id'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        if (nama != null) 'nama': nama,
        if (telepon != null) 'telepon': telepon,
        if (password != null) 'password': password,
        if (status != null) 'status': status,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Gagal mengupdate pelanggan');
    }
  }

  Future<void> deletePelanggan(int id) async {
    final response = await http.delete(Uri.parse('$_baseUrl/pelanggan/$id'));

    if (response.statusCode != 200) {
      throw Exception('Gagal menghapus pelanggan');
    }
  }
}
