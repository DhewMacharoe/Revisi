import 'dart:convert';

import 'package:http/http.dart' as http;

class AdminService {
  final String _baseUrl = 'http://localhost';

  // Get all admins
  Future<List<dynamic>> fetchAdmins() async {
    final response = await http.get(Uri.parse('$_baseUrl/admin'));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Gagal mengambil data admin');
    }
  }

  // Get admin by ID
  Future<Map<String, dynamic>> getAdminById(int id) async {
    final response = await http.get(Uri.parse('$_baseUrl/admin/$id'));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Admin tidak ditemukan');
    }
  }

  // Create new admin
  Future<Map<String, dynamic>> createAdmin({
    required String nama,
    required String email,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/admin'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'nama': nama,
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 201) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Gagal membuat admin');
    }
  }

  // Update admin
  Future<Map<String, dynamic>> updateAdmin({
    required int id,
    String? nama,
    String? email,
    String? password,
  }) async {
    final response = await http.put(
      Uri.parse('$_baseUrl/admin/$id'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        if (nama != null) 'nama': nama,
        if (email != null) 'email': email,
        if (password != null) 'password': password,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Gagal mengupdate admin');
    }
  }

  // Delete admin
  Future<void> deleteAdmin(int id) async {
    final response = await http.delete(Uri.parse('$_baseUrl/admin/$id'));

    if (response.statusCode != 200) {
      throw Exception('Gagal menghapus admin');
    }
  }

  // Admin login
  Future<Map<String, dynamic>> loginAdmin(String email, String password) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/admin/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Login gagal');
    }
  }
}
