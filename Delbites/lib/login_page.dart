import 'dart:convert';

import 'package:Delbites/main_screen.dart';
import 'package:Delbites/register_page.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000';

class LoginPage extends StatefulWidget {
  const LoginPage({Key? key}) : super(key: key);

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _namaController = TextEditingController();
  final TextEditingController _nomorController = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() {
    _namaController.dispose();
    _nomorController.dispose();
    super.dispose();
  }

  Future<void> _performLogin() async {
    setState(() {
      _isLoading = true;
    });

    final inputNama = _namaController.text.trim();
    final inputNomor = _nomorController.text.trim();

    if (inputNama.isEmpty || inputNomor.isEmpty) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Nama dan Nomor WhatsApp wajib diisi.'),
              backgroundColor: Colors.red),
        );
      }
      setState(() {
        _isLoading = false;
      });
      return;
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/pelanggan?nama=$inputNama&telepon=$inputNomor'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final List<dynamic> data = jsonDecode(response.body);

        if (data.isNotEmpty) {
          final Map<String, dynamic> pelangganData = data[0];

          final prefs = await SharedPreferences.getInstance();

          await prefs
              .clear(); // Bersihkan data lama sebelum menyimpan yang baru

          await prefs.setInt('id_pelanggan', pelangganData['id']);
          await prefs.setString('nama_pelanggan', pelangganData['nama']);
          await prefs.setString('telepon_pelanggan', pelangganData['telepon']);
          await prefs.setString(
              'email_pelanggan', pelangganData['email'] ?? '');
          await prefs.setBool(
              'isLoggedIn', true); // Tambahkan ini untuk status login

          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                  content: Text('Login berhasil!'),
                  backgroundColor: Colors.green),
            );
            Navigator.pushAndRemoveUntil(
              context,
              MaterialPageRoute(builder: (context) => const MainScreen()),
              (Route<dynamic> route) => false,
            );
          }
        } else {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                  content: Text('Nama atau Nomor WhatsApp tidak cocok.'),
                  backgroundColor: Colors.red),
            );
          }
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content:
                    Text('Gagal terhubung ke server: ${response.statusCode}'),
                backgroundColor: Colors.red),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Terjadi kesalahan saat login: $e'),
              backgroundColor: Colors.red),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Login',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const Text(
                    'Masuk ke Akun Anda',
                    style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 30),
                  TextFormField(
                    controller: _namaController,
                    decoration: const InputDecoration(
                      labelText: "Nama Lengkap",
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.person),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _nomorController,
                    decoration: const InputDecoration(
                      labelText: "Nomor WhatsApp",
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.phone),
                    ),
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 32),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _performLogin,
                      icon: const Icon(Icons.login, color: Colors.white),
                      label: const Text('Login',
                          style: TextStyle(color: Colors.white, fontSize: 16)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        padding: const EdgeInsets.symmetric(vertical: 15),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  TextButton(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (context) => const RegisterPage()),
                      );
                    },
                    child: const Text(
                      'Belum punya akun? Daftar di sini',
                      style: TextStyle(color: Color(0xFF2D5EA2), fontSize: 16),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
