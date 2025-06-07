import 'package:Delbites/edit_profile_page.dart'; // <-- Import halaman edit profil
import 'package:Delbites/login_page.dart'; // Import halaman login baru
import 'package:Delbites/main_screen.dart'; // Import MainScreen untuk navigasi setelah logout
import 'package:Delbites/register_page.dart'; // Import halaman register baru
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ProfilePage extends StatefulWidget {
  final int idPelanggan; // ID pelanggan yang diterima dari HomePage

  const ProfilePage({Key? key, required this.idPelanggan}) : super(key: key);

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  String _nama = 'Memuat...';
  String _telepon = 'Memuat...';
  String _email = 'Memuat...';
  bool _isLoading = true;
  bool _isLoggedIn = false; // Status login

  @override
  void initState() {
    super.initState();
    _loadProfileData(); // Memuat data profil saat inisialisasi
  }

  // Fungsi untuk memuat data profil dari SharedPreferences
  Future<void> _loadProfileData() async {
    setState(() {
      _isLoading = true; // Set loading true when reloading
    });
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      // Check if the widget is still in the tree
      setState(() {
        _nama = prefs.getString('nama_pelanggan') ?? 'Belum tersedia';
        _telepon = prefs.getString('telepon_pelanggan') ?? 'Belum tersedia';
        _email = prefs.getString('email_pelanggan') ?? 'Belum tersedia';
        _isLoggedIn = (prefs.getString('nama_pelanggan') != null &&
                prefs.getString('nama_pelanggan')!.isNotEmpty) ||
            (prefs.getInt('id_pelanggan') != null &&
                prefs.getInt('id_pelanggan')! != 0);
        _isLoading = false;
      });
    }
  }

  // Fungsi untuk menghapus data pelanggan dari SharedPreferences (Logout)
  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('id_pelanggan');
    await prefs.remove('nama_pelanggan');
    await prefs.remove('telepon_pelanggan');
    await prefs.remove('email_pelanggan');
    await prefs
        .remove('isLoggedIn'); // Clear login status if you set it elsewhere

    if (mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => const MainScreen()),
        (Route<dynamic> route) => false,
      );
    }
  }

  // Fungsi untuk navigasi ke halaman edit profil
  Future<void> _navigateToEditProfile() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const EditProfilePage()),
    );

    // Jika result adalah true, berarti ada perubahan yang disimpan
    if (result == true && mounted) {
      _loadProfileData(); // Muat ulang data profil untuk menampilkan perubahan
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
                              ).then((_) => _loadProfileData());
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
                              ).then((_) => _loadProfileData());
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
                  else // Jika sudah login, tampilkan detail profil
                    Expanded(
                      // Use Expanded to make the Column scrollable if content overflows
                      child: ListView(
                        // Changed Column to ListView for potential overflow
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
                          // Tombol Edit Profil
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: _navigateToEditProfile,
                              icon: const Icon(Icons.edit, color: Colors.white),
                              label: const Text('Edit Profil',
                                  style: TextStyle(color: Colors.white)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor:
                                    const Color(0xFF2D5EA2), // Blue color
                                padding:
                                    const EdgeInsets.symmetric(vertical: 12),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 10), // Space between buttons
                          // Tombol Logout
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
