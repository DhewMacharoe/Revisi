import 'dart:convert'; // Import untuk jsonDecode

import 'package:Delbites/main_screen.dart'; // Untuk navigasi kembali ke MainScreen
import 'package:Delbites/register_page.dart'; // Untuk navigasi ke halaman register
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http; // Import package http
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'https://delbites.d4trpl-itdel.id'; // Base URL API Anda

class LoginPage extends StatefulWidget {
  const LoginPage({Key? key}) : super(key: key);

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _namaController = TextEditingController();
  final TextEditingController _nomorController =
      TextEditingController(); // Menambahkan controller untuk nomor HP
  bool _isLoading = false;

  @override
  void dispose() {
    _namaController.dispose();
    _nomorController.dispose(); // Dispose controller nomor HP
    super.dispose();
  }

  // Fungsi untuk melakukan login dengan API
  Future<void> _performLogin() async {
    setState(() {
      _isLoading = true;
    });

    // Ambil input dari user
    final inputNama = _namaController.text.trim();
    final inputNomor = _nomorController.text.trim();

    // Validasi input
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
      // Melakukan panggilan API untuk mencari pelanggan berdasarkan nama dan nomor telepon
      // PENTING: Pastikan API ini mengembalikan kolom 'email' dalam responsnya.
      // Contoh: {"id":..., "nama":"...", "telepon":"...", "email":"...", ...}
      final response = await http.get(
        Uri.parse('$baseUrl/api/pelanggan?nama=$inputNama&telepon=$inputNomor'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final List<dynamic> data = jsonDecode(response.body);

        // Periksa apakah ada pelanggan yang cocok
        if (data.isNotEmpty) {
          final Map<String, dynamic> pelangganData =
              data[0]; // Ambil data pelanggan pertama yang cocok

          // Simpan data pelanggan ke SharedPreferences
          final prefs = await SharedPreferences.getInstance();
          await prefs.setInt('id_pelanggan', pelangganData['id']);
          await prefs.setString('nama_pelanggan', pelangganData['nama']);
          await prefs.setString('telepon_pelanggan', pelangganData['telepon']);
          // Simpan email yang diterima dari API. Jika API tidak mengembalikan 'email', ini akan menjadi string kosong.
          await prefs.setString(
              'email_pelanggan', pelangganData['email'] ?? '');

          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                  content: Text('Login berhasil!'),
                  backgroundColor: Colors.green),
            );
            // Navigasi kembali ke MainScreen dan hapus semua rute sebelumnya
            Navigator.pushAndRemoveUntil(
              context,
              MaterialPageRoute(builder: (context) => const MainScreen()),
              (Route<dynamic> route) => false,
            );
          }
        } else {
          // Jika tidak ada pelanggan yang cocok
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                  content: Text('Nama atau Nomor WhatsApp tidak cocok.'),
                  backgroundColor: Colors.red),
            );
          }
        }
      } else {
        // Tangani jika respons API bukan 200 OK
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
                  // Input untuk Nama
                  TextFormField(
                    controller: _namaController,
                    decoration: const InputDecoration(
                      labelText: "Nama Lengkap",
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.person),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Nama wajib diisi';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  // Input untuk Nomor WhatsApp
                  TextFormField(
                    controller: _nomorController,
                    decoration: const InputDecoration(
                      labelText: "Nomor WhatsApp",
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.phone),
                    ),
                    keyboardType: TextInputType.phone,
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Nomor WhatsApp wajib diisi';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 32),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed:
                          _performLogin, // Panggil fungsi login yang baru
                      icon: const Icon(Icons.login, color: Colors.white),
                      label: const Text('Login',
                          style: TextStyle(color: Colors.white, fontSize: 16)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor:
                            Colors.green, // Warna hijau untuk login
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
