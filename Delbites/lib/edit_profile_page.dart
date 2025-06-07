import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

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
  String _initialNama = '';
  String _initialTelepon = '';
  String _initialEmail = '';

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
    setState(() {
      _initialNama = prefs.getString('nama_pelanggan') ?? '';
      _initialTelepon = prefs.getString('telepon_pelanggan') ?? '';
      _initialEmail = prefs.getString('email_pelanggan') ?? '';

      _namaController.text = _initialNama;
      _teleponController.text = _initialTelepon;
      _emailController.text = _initialEmail;
      _isLoading = false;
    });
  }

  Future<void> _saveProfileChanges() async {
    if (_formKey.currentState!.validate()) {
      setState(() {
        _isLoading = true; // Show loading indicator while saving
      });

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('nama_pelanggan', _namaController.text);
      await prefs.setString('telepon_pelanggan', _teleponController.text);
      await prefs.setString('email_pelanggan', _emailController.text);

      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profil berhasil diperbarui!')),
        );
        Navigator.pop(
            context, true); // Return true to indicate changes were made
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
                  // Use ListView for scrollability if content overflows
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
                        // Basic phone validation (optional, can be more complex)
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
