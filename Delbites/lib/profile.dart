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
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _nama = prefs.getString('nama_pelanggan') ?? 'Belum tersedia';
      _telepon = prefs.getString('telepon_pelanggan') ?? 'Belum tersedia';
      _email = prefs.getString('email_pelanggan') ?? 'Belum tersedia';
      // Tentukan status login berdasarkan ketersediaan nama pelanggan atau idPelanggan
      _isLoggedIn = (prefs.getString('nama_pelanggan') != null &&
              prefs.getString('nama_pelanggan')!.isNotEmpty) ||
          (prefs.getInt('id_pelanggan') != null &&
              prefs.getInt('id_pelanggan')! != 0); // Periksa juga idPelanggan
      _isLoading = false;
    });
  }

  // Fungsi untuk menghapus data pelanggan dari SharedPreferences (Logout)
  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('id_pelanggan');
    await prefs.remove('nama_pelanggan');
    await prefs.remove('telepon_pelanggan');
    await prefs.remove('email_pelanggan');

    // Navigasi kembali ke MainScreen dan hapus semua rute sebelumnya
    if (mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => const MainScreen()),
        (Route<dynamic> route) => false, // Hapus semua rute sebelumnya
      );
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
        iconTheme:
            const IconThemeData(color: Colors.white), // Warna ikon kembali
      ),
      body: _isLoading
          ? const Center(
              child:
                  CircularProgressIndicator()) // Tampilkan loading jika data masih dimuat
          : Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Bagian Login/Informasi Pengguna
                  if (!_isLoggedIn) // Jika belum login, tampilkan pesan dan tombol login/register
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
                              ).then((_) =>
                                  _loadProfileData()); // Muat ulang data setelah kembali
                            },
                            icon: const Icon(Icons.login, color: Colors.white),
                            label: const Text('Login',
                                style: TextStyle(color: Colors.white)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors
                                  .green, // Warna hijau untuk tombol login
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 10), // Spasi antara tombol
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (context) => const RegisterPage()),
                              ).then((_) =>
                                  _loadProfileData()); // Muat ulang data setelah kembali
                            },
                            icon: const Icon(Icons.app_registration,
                                color: Colors.white), // Ikon registrasi
                            label: const Text('Register',
                                style: TextStyle(color: Colors.white)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(
                                  0xFF2D5EA2), // Warna biru untuk tombol register
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
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Bagian Nama
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
                        // Bagian Nomor Telepon
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
                        // Bagian Email
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
                        // Tombol Logout
                        SizedBox(
                          width: double
                              .infinity, // Membuat tombol mengisi lebar penuh
                          child: ElevatedButton.icon(
                            onPressed:
                                _logout, // Panggil fungsi logout saat ditekan
                            icon: const Icon(Icons.logout,
                                color: Colors.white), // Ikon logout
                            label: const Text('Logout',
                                style: TextStyle(
                                    color: Colors.white)), // Teks tombol
                            style: ElevatedButton.styleFrom(
                              backgroundColor:
                                  Colors.red, // Warna latar belakang merah
                              padding: const EdgeInsets.symmetric(
                                  vertical: 12), // Padding vertikal
                              shape: RoundedRectangleBorder(
                                borderRadius:
                                    BorderRadius.circular(8), // Sudut membulat
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                ],
              ),
            ),
    );
  }
}
