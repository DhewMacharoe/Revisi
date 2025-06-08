import 'dart:convert';

import 'package:Delbites/main_screen.dart'; // Import MainScreen
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000';

class RegisterPage extends StatefulWidget {
  const RegisterPage({Key? key}) : super(key: key);

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _namaController = TextEditingController();
  final TextEditingController _nomorController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
  }

  @override
  void dispose() {
    _namaController.dispose();
    _nomorController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<int> _registerPelanggan(
      String nama, String telepon, String email) async {
    final createResponse = await http.post(
      Uri.parse('$baseUrl/api/pelanggan'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: jsonEncode({
        'nama': nama,
        'telepon': telepon,
        'email': email,
      }),
    );

    if (createResponse.statusCode == 200 || createResponse.statusCode == 201) {
      final created = jsonDecode(createResponse.body);
      final id = created['id'] ?? created['data']?['id'];

      if (id == null) {
        throw Exception(
            'ID pelanggan tidak ditemukan dalam response: ${createResponse.body}');
      }

      final prefs = await SharedPreferences.getInstance();
      await prefs
          .clear(); // Bersihkan SharedPreferences sebelum menyimpan data user baru

      await prefs.setInt('id_pelanggan', id is String ? int.parse(id) : id);
      await prefs.setString('nama_pelanggan', nama);
      await prefs.setString('telepon_pelanggan', telepon);
      await prefs.setString('email_pelanggan', email);
      await prefs.setBool('isLoggedIn', true); // Set status login

      return id is String ? int.parse(id) : id;
    } else {
      String errorMessage = 'Gagal mendaftar pelanggan baru.';
      try {
        final errorBody = jsonDecode(createResponse.body);
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
        } else if (errorBody['error'] != null) {
          errorMessage = errorBody['error'];
        } else {
          errorMessage =
              'Error: ${createResponse.statusCode} - ${createResponse.reasonPhrase}. Body: ${createResponse.body}';
        }
      } catch (e) {
        errorMessage =
            'Error: ${createResponse.statusCode} - ${createResponse.reasonPhrase}. Body: ${createResponse.body}';
      }
      throw Exception(errorMessage);
    }
  }

  Future<void> _savePelangganData() async {
    if (_formKey.currentState!.validate()) {
      setState(() {
        _isLoading = true;
      });
      try {
        final nama = _namaController.text.trim();
        final telepon = _nomorController.text.trim();
        final email = _emailController.text.trim();

        await _registerPelanggan(nama, telepon, email);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text('Registrasi berhasil! Anda telah login.'),
                backgroundColor: Colors.green),
          );
          // Setelah registrasi berhasil dan login, arahkan ke MainScreen
          Navigator.pushAndRemoveUntil(
            context,
            MaterialPageRoute(builder: (context) => const MainScreen()),
            (Route<dynamic> route) => false,
          );
        }
      } catch (e) {
        print('Error saving pelanggan data: $e');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content: Text(
                    'Gagal registrasi: ${e.toString().replaceFirst("Exception: ", "")}'),
                backgroundColor: Colors.red),
          );
        }
      } finally {
        if (mounted) {
          setState(() {
            _isLoading = false;
          });
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Daftar Akun Pelanggan',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    TextFormField(
                      controller: _namaController,
                      decoration: const InputDecoration(
                        labelText: "Nama Lengkap",
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.person),
                      ),
                      validator: (value) =>
                          (value == null || value.trim().isEmpty)
                              ? 'Nama wajib diisi'
                              : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _nomorController,
                      decoration: const InputDecoration(
                        labelText: "Nomor WhatsApp",
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.phone),
                        hintText: 'Contoh: 081234567890',
                      ),
                      keyboardType: TextInputType.phone,
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Nomor HP wajib diisi';
                        }
                        if (!RegExp(r'^08[0-9]{8,11}$')
                            .hasMatch(value.trim())) {
                          return 'Format nomor HP tidak valid. Contoh: 081234567890';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _emailController,
                      decoration: const InputDecoration(
                        labelText: "Email",
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.email),
                        hintText: 'contoh@email.com',
                      ),
                      keyboardType: TextInputType.emailAddress,
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Email wajib diisi';
                        }
                        final emailRegex =
                            RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$');
                        if (!emailRegex.hasMatch(value.trim())) {
                          return 'Format email tidak valid';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 32),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: _isLoading ? null : _savePelangganData,
                        icon: const Icon(Icons.app_registration,
                            color: Colors.white),
                        label: const Text('Daftar Akun',
                            style:
                                TextStyle(color: Colors.white, fontSize: 16)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2D5EA2),
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
