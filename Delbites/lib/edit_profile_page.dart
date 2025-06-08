import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000'; // Pastikan ini sesuai

class EditProfilePage extends StatefulWidget {
  const EditProfilePage({Key? key}) : super(key: key);

  @override
  State<EditProfilePage> createState() => _EditProfilePageState();
}

class _EditProfilePageState extends State<EditProfilePage> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _namaController;
  late TextEditingController _teleponController;
  late TextEditingController _emailController;

  bool _isLoading = true;
  int? _pelangganId; // Tambahkan untuk menyimpan ID pelanggan

  @override
  void initState() {
    super.initState();
    _namaController = TextEditingController();
    _teleponController = TextEditingController();
    _emailController = TextEditingController();
    _loadCurrentProfileData();
  }

  Future<void> _loadCurrentProfileData() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _pelangganId = prefs.getInt('id_pelanggan'); // Ambil ID pelanggan
        _namaController.text = prefs.getString('nama_pelanggan') ?? '';
        _teleponController.text = prefs.getString('telepon_pelanggan') ?? '';
        _emailController.text = prefs.getString('email_pelanggan') ?? '';
        _isLoading = false;
      });
    }
  }

  Future<void> _saveProfileChanges() async {
    if (_formKey.currentState!.validate()) {
      if (_pelangganId == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text(
                    'ID Pelanggan tidak ditemukan. Tidak dapat menyimpan.'),
                backgroundColor: Colors.red),
          );
        }
        return;
      }

      setState(() {
        _isLoading = true;
      });

      try {
        final response = await http.put(
          Uri.parse(
              '$baseUrl/api/pelanggan/${_pelangganId}'), // Endpoint PUT/PATCH
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonEncode({
            'nama': _namaController.text,
            'telepon': _teleponController.text,
            'email': _emailController.text,
          }),
        );

        if (response.statusCode == 200) {
          final Map<String, dynamic> updatedData = jsonDecode(response.body);
          // Perbarui SharedPreferences dengan data terbaru dari API
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('nama_pelanggan', updatedData['nama']);
          await prefs.setString('telepon_pelanggan', updatedData['telepon']);
          await prefs.setString('email_pelanggan', updatedData['email'] ?? '');

          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                  content: Text('Profil berhasil diperbarui!'),
                  backgroundColor: Colors.green),
            );
            Navigator.pop(context,
                true); // Kembali dan beri tahu ProfilePage untuk refresh
          }
        } else {
          String errorMessage = 'Gagal memperbarui profil.';
          try {
            final errorBody = jsonDecode(response.body);
            if (errorBody['message'] != null) {
              errorMessage = errorBody['message'];
            } else if (errorBody['errors'] != null &&
                errorBody['errors'] is Map) {
              Map<String, dynamic> errors = errorBody['errors'];
              StringBuffer messages = StringBuffer();
              errors.forEach((key, value) {
                if (value is List && value.isNotEmpty) {
                  messages.writeln("${value[0]}");
                }
              });
              if (messages.isNotEmpty)
                errorMessage = messages.toString().trim();
            }
          } catch (e) {
            // Jika respons bukan JSON atau ada error parsing
            errorMessage = 'Gagal memperbarui profil: ${response.statusCode}';
          }

          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                  content: Text(errorMessage), backgroundColor: Colors.red),
            );
          }
        }
      } catch (e) {
        print('Error saving profile data: $e');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content: Text('Terjadi kesalahan: $e'),
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
  void dispose() {
    _namaController.dispose();
    _teleponController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Edit Profil',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [
                    TextFormField(
                      controller: _namaController,
                      decoration: const InputDecoration(
                        labelText: 'Nama',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.person),
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Nama tidak boleh kosong';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _teleponController,
                      decoration: const InputDecoration(
                        labelText: 'Nomor Telepon',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.phone),
                      ),
                      keyboardType: TextInputType.phone,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Nomor telepon tidak boleh kosong';
                        }
                        if (!RegExp(r'^[0-9\+\-\s]+$').hasMatch(value)) {
                          return 'Format nomor telepon tidak valid';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _emailController,
                      decoration: const InputDecoration(
                        labelText: 'Email',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.email),
                      ),
                      keyboardType: TextInputType.emailAddress,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Email tidak boleh kosong';
                        }
                        if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value)) {
                          return 'Format email tidak valid';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton.icon(
                      onPressed: _saveProfileChanges,
                      icon: const Icon(Icons.save, color: Colors.white),
                      label: const Text('Simpan Perubahan',
                          style: TextStyle(color: Colors.white)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        textStyle: const TextStyle(fontSize: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
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
