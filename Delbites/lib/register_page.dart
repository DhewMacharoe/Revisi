import 'dart:convert';
import 'dart:io';

import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'https://delbites.d4trpl-itdel.id';

// Fungsi untuk mendapatkan Device ID
Future<String> getDeviceId() async {
  final deviceInfo = DeviceInfoPlugin();
  if (Platform.isAndroid) {
    final info = await deviceInfo.androidInfo;
    return info.id;
  } else if (Platform.isIOS) {
    final info = await deviceInfo.iosInfo;
    return info.identifierForVendor ?? '';
  }
  return '';
}

class RegisterPage extends StatefulWidget {
  // Nama kelas diubah menjadi RegisterPage
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
    _loadExistingData(); // Memuat data yang sudah ada saat inisialisasi
  }

  @override
  void dispose() {
    _namaController.dispose();
    _nomorController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  // Memuat data pelanggan yang sudah ada dari SharedPreferences
  Future<void> _loadExistingData() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _namaController.text = prefs.getString('nama_pelanggan') ?? '';
      _nomorController.text = prefs.getString('telepon_pelanggan') ?? '';
      _emailController.text = prefs.getString('email_pelanggan') ?? '';
    });
  }

  // Fungsi untuk mendapatkan atau membuat ID pelanggan (digunakan untuk registrasi)
  Future<int> _registerPelanggan(
      String nama, String telepon, String email) async {
    final prefs = await SharedPreferences.getInstance();
    final deviceId = await getDeviceId();
    if (!prefs.containsKey('device_id')) {
      await prefs.setString('device_id', deviceId);
    }

    // Buat pelanggan baru
    final createResponse = await http.post(
      Uri.parse('$baseUrl/api/pelanggan'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'nama': nama,
        'telepon': telepon,
        'email': email,
        'device_id': deviceId,
      }),
    );

    if (createResponse.statusCode == 200 || createResponse.statusCode == 201) {
      final created = jsonDecode(createResponse.body);
      final id = created['id'];
      await prefs.setInt('id_pelanggan', id);
      await prefs.setString('nama_pelanggan', nama);
      await prefs.setString('telepon_pelanggan', telepon);
      await prefs.setString('email_pelanggan', email);
      return id;
    } else {
      throw Exception('Gagal mendaftar pelanggan baru: ${createResponse.body}');
    }
  }

  // Fungsi untuk menyimpan data pelanggan (sekarang untuk registrasi)
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
                content: Text('Registrasi berhasil!'),
                backgroundColor: Colors.green),
          );
          // Navigasi kembali setelah data disimpan
          Navigator.pop(
              context, true); // Mengirimkan 'true' sebagai hasil sukses
        }
      } catch (e) {
        print('Error saving pelanggan data: $e');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content: Text('Gagal registrasi data pelanggan: $e'),
                backgroundColor: Colors.red),
          );
        }
      } finally {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Daftar Akun Pelanggan', // Judul diubah
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
                      ),
                      keyboardType: TextInputType.phone,
                      validator: (value) =>
                          (value == null || value.trim().isEmpty)
                              ? 'Nomor HP wajib diisi'
                              : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _emailController,
                      decoration: const InputDecoration(
                        labelText: "Email",
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.email),
                      ),
                      keyboardType: TextInputType.emailAddress,
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Email wajib diisi';
                        }
                        final emailRegex = RegExp(r'^[^@]+@[^@]+\.[^@]+');
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
                        onPressed: _savePelangganData,
                        icon: const Icon(Icons.app_registration,
                            color: Colors.white), // Ikon registrasi
                        label: const Text('Daftar Akun',
                            style: TextStyle(
                                color: Colors.white,
                                fontSize: 16)), // Teks diubah
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
