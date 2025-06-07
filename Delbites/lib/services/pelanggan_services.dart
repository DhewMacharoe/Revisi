import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class PelangganService {
  final String _baseUrl = 'http://127.0.0.1:8000/api/pelanggan';

  Future<List<dynamic>> fetchPelanggan() async {
    final response = await http.get(Uri.parse(_baseUrl));

    if (response.statusCode == 200) {
      final decodedBody = jsonDecode(response.body);
      if (decodedBody is List) {
        return decodedBody;
      } else if (decodedBody is Map &&
          decodedBody.containsKey('data') &&
          decodedBody['data'] is List) {
        return decodedBody['data'];
      } else {
        throw Exception(
            'Format respons tidak terduga saat mengambil data pelanggan');
      }
    } else {
      throw Exception(
          'Gagal mengambil data pelanggan: ${response.statusCode} ${response.body}');
    }
  }

  Future<Map<String, dynamic>> getPelangganById(int id) async {
    final response = await http.get(Uri.parse('$_baseUrl/$id'));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception(
          'Pelanggan tidak ditemukan: ${response.statusCode} ${response.body}');
    }
  }

  Future<Map<String, dynamic>> createPelanggan({
    required String nama,
    required String telepon,
    String? email,
  }) async {
    final url = Uri.parse(_baseUrl);

    Map<String, String> body = {
      'nama': nama,
      'telepon': telepon,
    };

    if (email != null && email.isNotEmpty) {
      body['email'] = email;
    }

    final response = await http.post(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: jsonEncode(body),
    );

    if (response.statusCode == 201 || response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final pelangganData = data['data'] ?? data;

      if (pelangganData['id'] == null || pelangganData['nama'] == null) {
        throw Exception(
            'Respons tidak lengkap dari server setelah membuat pelanggan: ${response.body}');
      }

      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt(
          'id_pelanggan',
          pelangganData['id'] is String
              ? int.parse(pelangganData['id'])
              : pelangganData['id']);
      await prefs.setString('nama_pelanggan', pelangganData['nama']);
      await prefs.setString(
          'telepon_pelanggan', pelangganData['telepon'] ?? '');
      if (pelangganData['email'] != null) {
        await prefs.setString('email_pelanggan', pelangganData['email']);
      }
      await prefs.setBool('isLoggedIn', true);

      return pelangganData;
    } else {
      String errorMessage = 'Gagal membuat pelanggan.';
      try {
        final errorBody = jsonDecode(response.body);
        if (errorBody['message'] != null) {
          errorMessage = errorBody['message'];
        } else if (errorBody['errors'] != null && errorBody['errors'] is Map) {
          Map<String, dynamic> errors = errorBody['errors'];
          StringBuffer messages = StringBuffer();
          errors.forEach((key, value) {
            if (value is List && value.isNotEmpty) {
              messages.writeln("${value[0]}");
            }
          });
          if (messages.isNotEmpty) errorMessage = messages.toString().trim();
        } else {
          errorMessage =
              'Error: ${response.statusCode}. Body: ${response.body}';
        }
      } catch (e) {
        errorMessage = 'Error: ${response.statusCode}. Body: ${response.body}';
      }
      throw Exception(errorMessage);
    }
  }

  Future<Map<String, dynamic>> updatePelanggan({
    required int id,
    String? nama,
    String? telepon,
    String? email,
  }) async {
    Map<String, String> body = {};
    if (nama != null) body['nama'] = nama;
    if (telepon != null) body['telepon'] = telepon;
    if (email != null) body['email'] = email;

    if (body.isEmpty) {
      return getPelangganById(id);
    }

    final response = await http.put(
      Uri.parse('$_baseUrl/$id'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: jsonEncode(body),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final pelangganData = data['data'] ?? data;

      final prefs = await SharedPreferences.getInstance();
      int? currentUserId = prefs.getInt('id_pelanggan');
      if (currentUserId == id) {
        if (pelangganData['nama'] != null)
          await prefs.setString('nama_pelanggan', pelangganData['nama']);
        if (pelangganData['telepon'] != null)
          await prefs.setString('telepon_pelanggan', pelangganData['telepon']);
        if (pelangganData['email'] != null)
          await prefs.setString('email_pelanggan', pelangganData['email']);
      }
      return pelangganData;
    } else {
      throw Exception(
          'Gagal mengupdate pelanggan: ${response.statusCode} ${response.body}');
    }
  }

  Future<void> deletePelanggan(int id) async {
    final response = await http.delete(Uri.parse('$_baseUrl/$id'));

    if (response.statusCode != 200 && response.statusCode != 204) {
      throw Exception(
          'Gagal menghapus pelanggan: ${response.statusCode} ${response.body}');
    }
  }
}
