import 'dart:convert';

import 'package:Delbites/edit_profile_page.dart';
import 'package:Delbites/login_page.dart';
import 'package:Delbites/main_screen.dart';
import 'package:Delbites/register_page.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000';

class ProfilePage extends StatefulWidget {
  final int idPelanggan;

  const ProfilePage({Key? key, required this.idPelanggan}) : super(key: key);

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  String _nama = 'Memuat...';
  String _telepon = 'Memuat...';
  String _email = 'Memuat...';
  bool _isLoading = true;
  bool _isLoggedIn = false;

  @override
  void initState() {
    super.initState();
    _checkLoginStatusAndLoadProfile();
  }

  Future<void> _checkLoginStatusAndLoadProfile() async {
    final prefs = await SharedPreferences.getInstance();
    final int? storedId = prefs.getInt('id_pelanggan');
    final bool? loggedInStatus = prefs.getBool('isLoggedIn');

    if (mounted) {
      setState(() {
        _isLoggedIn = (storedId != null && loggedInStatus == true);
      });
    }

    if (_isLoggedIn) {
      await _fetchProfileDataFromApi(storedId!);
    } else {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _fetchProfileDataFromApi(int idPelanggan) async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/pelanggan/$idPelanggan'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            _nama = data['nama'] ?? 'Belum tersedia';
            _telepon = data['telepon'] ?? 'Belum tersedia';
            _email = data['email'] ?? 'Belum tersedia';
          });
          // Optional: Update SharedPreferences with latest data from API
          // This serves as a cache, not the primary source of truth for display
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('nama_pelanggan', _nama);
          await prefs.setString('telepon_pelanggan', _telepon);
          await prefs.setString('email_pelanggan', _email);
        }
      } else {
        // Fallback to SharedPreferences data if API call fails
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content: Text(
                    'Failed to load profile from server: ${response.statusCode}'),
                backgroundColor: Colors.red),
          );
        }
        final prefs = await SharedPreferences.getInstance();
        if (mounted) {
          setState(() {
            _nama = prefs.getString('nama_pelanggan') ?? 'Not available';
            _telepon = prefs.getString('telepon_pelanggan') ?? 'Not available';
            _email = prefs.getString('email_pelanggan') ?? 'Not available';
          });
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('An error occurred while loading profile: $e'),
              backgroundColor: Colors.red),
        );
      }
      // Fallback to SharedPreferences data if error occurs
      final prefs = await SharedPreferences.getInstance();
      if (mounted) {
        setState(() {
          _nama = prefs.getString('nama_pelanggan') ?? 'Not available';
          _telepon = prefs.getString('telepon_pelanggan') ?? 'Not available';
          _email = prefs.getString('email_pelanggan') ?? 'Not available';
        });
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();

    if (mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => const MainScreen()),
        (Route<dynamic> route) => false,
      );
    }
  }

  Future<void> _navigateToEditProfile() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const EditProfilePage()),
    );

    if (result == true && mounted) {
      final prefs = await SharedPreferences.getInstance();
      final int? storedId = prefs.getInt('id_pelanggan');
      if (storedId != null) {
        _fetchProfileDataFromApi(storedId);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Profil Anda',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (!_isLoggedIn)
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Anda belum login.',
                          style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.red),
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (context) => const LoginPage()),
                              ).then((_) => _checkLoginStatusAndLoadProfile());
                            },
                            icon: const Icon(Icons.login, color: Colors.white),
                            label: const Text('Login',
                                style: TextStyle(color: Colors.white)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.green,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (context) => const RegisterPage()),
                              ).then((_) => _checkLoginStatusAndLoadProfile());
                            },
                            icon: const Icon(Icons.app_registration,
                                color: Colors.white),
                            label: const Text('Register',
                                style: TextStyle(color: Colors.white)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF2D5EA2),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),
                      ],
                    )
                  else
                    Expanded(
                      child: ListView(
                        children: [
                          Card(
                            margin: const EdgeInsets.symmetric(vertical: 8.0),
                            child: ListTile(
                              leading: const Icon(Icons.person,
                                  color: Color(0xFF2D5EA2)),
                              title: const Text('Nama'),
                              subtitle: Text(_nama,
                                  style: const TextStyle(fontSize: 16)),
                            ),
                          ),
                          Card(
                            margin: const EdgeInsets.symmetric(vertical: 8.0),
                            child: ListTile(
                              leading: const Icon(Icons.phone,
                                  color: Color(0xFF2D5EA2)),
                              title: const Text('Nomor Telepon'),
                              subtitle: Text(_telepon,
                                  style: const TextStyle(fontSize: 16)),
                            ),
                          ),
                          Card(
                            margin: const EdgeInsets.symmetric(vertical: 8.0),
                            child: ListTile(
                              leading: const Icon(Icons.email,
                                  color: Color(0xFF2D5EA2)),
                              title: const Text('Email'),
                              subtitle: Text(_email,
                                  style: const TextStyle(fontSize: 16)),
                            ),
                          ),
                          const SizedBox(height: 20),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: _navigateToEditProfile,
                              icon: const Icon(Icons.edit, color: Colors.white),
                              label: const Text('Edit Profil',
                                  style: TextStyle(color: Colors.white)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF2D5EA2),
                                padding:
                                    const EdgeInsets.symmetric(vertical: 12),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 10),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: _logout,
                              icon:
                                  const Icon(Icons.logout, color: Colors.white),
                              label: const Text('Logout',
                                  style: TextStyle(color: Colors.white)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.red,
                                padding:
                                    const EdgeInsets.symmetric(vertical: 12),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
    );
  }
}
