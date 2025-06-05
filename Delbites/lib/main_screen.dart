import 'dart:convert'; // Import untuk decoding JSON

import 'package:Delbites/closed_app_page.dart'; // Import halaman ClosedAppPage
import 'package:Delbites/home.dart';
import 'package:Delbites/keranjang.dart';
import 'package:Delbites/riwayat_pesanan.dart';
import 'package:curved_navigation_bar/curved_navigation_bar.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http; // Import untuk panggilan API
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'https://delbites.d4trpl-itdel.id'; // Base URL API Anda

class MainScreen extends StatefulWidget {
  const MainScreen({Key? key}) : super(key: key);

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> with WidgetsBindingObserver {
  // Tambahkan with WidgetsBindingObserver
  int _currentIndex = 0;
  final PageController _pageController = PageController();
  int? _idPelanggan; // Variabel untuk menyimpan ID pelanggan
  bool _isLoading =
      true; // Status untuk menunjukkan apakah data sedang dimuat (termasuk status aplikasi & pelanggan)

  // State untuk status aplikasi dari backend
  bool _appIsOpen = false; // Default false, akan diperbarui dari API
  String _appCloseMessage =
      'Aplikasi sedang tidak tersedia. Mohon coba lagi nanti.';

  // Daftar halaman yang akan ditampilkan di PageView
  List<Widget> _pages = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this); // Daftarkan observer
    _checkAndLoadAppStatus(); // Panggil fungsi ini saat initState
  }

  @override
  void dispose() {
    WidgetsBinding.instance
        .removeObserver(this); // Hapus observer saat widget dibuang
    _pageController.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    // Memuat ulang status aplikasi saat aplikasi kembali ke foreground
    if (state == AppLifecycleState.resumed) {
      _checkAndLoadAppStatus();
    }
  }

  // Fungsi untuk memeriksa status aplikasi dari backend dan memuat data pelanggan
  Future<void> _checkAndLoadAppStatus() async {
    print('DEBUG: _checkAndLoadAppStatus dimulai.');
    setState(() {
      _isLoading = true; // Mulai loading untuk status aplikasi
    });
    try {
      // Panggil endpoint menu atau endpoint lain yang sering diakses
      // Admin akan mengubah respons dari endpoint ini untuk mengontrol status aplikasi
      final response = await http.get(Uri.parse(
          '$baseUrl/api/menu')); // Contoh: Menggunakan endpoint /api/menu
      print('DEBUG: Respons API status code: ${response.statusCode}');
      print('DEBUG: Respons API body: ${response.body}');

      if (response.statusCode == 200) {
        final dynamic decodedResponse =
            json.decode(response.body); // Dekode sebagai dynamic
        print(
            'DEBUG: Tipe respons yang didekode: ${decodedResponse.runtimeType}');

        // Prioritaskan pengecekan untuk status 'closed' yang dikirim sebagai Map
        if (decodedResponse is Map<String, dynamic> &&
            decodedResponse.containsKey('app_status') &&
            decodedResponse['app_status'] == 'closed') {
          print('DEBUG: Status aplikasi: TUTUP (dari respons Map).');
          setState(() {
            _appIsOpen = false;
            _appCloseMessage = decodedResponse['message'] ??
                'Aplikasi sedang dalam pemeliharaan.';
          });
        }
        // Jika respons adalah List (daftar menu), anggap aplikasi terbuka
        else if (decodedResponse is List<dynamic>) {
          print('DEBUG: Status aplikasi: BUKA (menerima daftar menu).');
          setState(() {
            _appIsOpen = true; // Aplikasi terbuka
          });
          await _loadPelangganInfo(); // Lanjutkan memuat info pelanggan hanya jika aplikasi terbuka
        }
        // Tangani kasus lain yang tidak terduga, anggap aplikasi tutup untuk keamanan
        else {
          print(
              'DEBUG: Respons API tidak terduga. Menganggap aplikasi TUTUP untuk keamanan.');
          setState(() {
            _appIsOpen = false;
            _appCloseMessage =
                'Terjadi kesalahan pada respons server. Mohon coba lagi nanti.';
          });
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                  content:
                      Text('Respons server tidak valid. Aplikasi ditutup.')),
            );
          }
        }
      } else {
        // Jika respons bukan 200 OK, anggap aplikasi sedang bermasalah atau tutup
        print('DEBUG: Respons API bukan 200 OK.');
        setState(() {
          _appIsOpen = false;
          _appCloseMessage =
              'Gagal terhubung ke server. Kode: ${response.statusCode}. Mohon coba lagi nanti.';
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content: Text(
                    'Gagal memeriksa status aplikasi: ${response.statusCode}.')),
          );
        }
      }
    } catch (e) {
      // Tangani kesalahan jaringan atau parsing
      print('DEBUG: Error di blok catch _checkAndLoadAppStatus: $e');
      setState(() {
        _appIsOpen = false;
        _appCloseMessage =
            'Terjadi kesalahan jaringan atau parsing: $e. Mohon coba lagi nanti.';
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error jaringan/parsing: $e')),
        );
      }
    } finally {
      print('DEBUG: Blok finally _checkAndLoadAppStatus.');
      setState(() {
        _isLoading = false; // Selesai loading status aplikasi
      });
    }
  }

  // Fungsi untuk memuat ID pelanggan dari SharedPreferences
  Future<void> _loadPelangganInfo() async {
    print('DEBUG: _loadPelangganInfo dimulai.');
    try {
      final prefs = await SharedPreferences.getInstance();
      setState(() {
        _idPelanggan = prefs.getInt('id_pelanggan');
        print('DEBUG: idPelanggan dimuat: $_idPelanggan');
        _initializePages(); // Inisialisasi daftar halaman setelah idPelanggan dimuat
        print('DEBUG: Halaman diinisialisasi.');
      });
    } catch (e) {
      print('DEBUG: Error di blok catch _loadPelangganInfo: $e');
      setState(() {
        _idPelanggan =
            0; // Atur ID pelanggan ke 0 sebagai fallback jika gagal memuat
        _initializePages(); // Tetap inisialisasi halaman dengan ID fallback
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat info pelanggan: $e')),
        );
      }
    }
    // Tidak ada finally di sini karena _isLoading dikelola oleh _checkAndLoadAppStatus
    print('DEBUG: _loadPelangganInfo selesai.');
  }

  // Fungsi untuk menginisialisasi daftar halaman dengan idPelanggan yang sudah dimuat
  void _initializePages() {
    _pages = [
      const HomePage(),
      const RiwayatPesananPage(),
      KeranjangPage(idPelanggan: _idPelanggan ?? 0),
    ];
  }

  // Fungsi yang dipanggil saat item navigasi ditekan
  void _onTap(int index) {
    setState(() {
      _currentIndex = index;
    });
    _pageController.animateToPage(
      index,
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
    );
  }

  @override
  Widget build(BuildContext context) {
    // Tampilkan indikator loading jika data masih dimuat (status aplikasi atau pelanggan)
    if (_isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    // Jika aplikasi tidak terbuka, tampilkan ClosedAppPage
    if (!_appIsOpen) {
      return ClosedAppPage(message: _appCloseMessage);
    }

    // Jika aplikasi terbuka, tampilkan konten MainScreen normal
    return Scaffold(
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(),
        children: _pages,
      ),
      bottomNavigationBar: CurvedNavigationBar(
        index: _currentIndex,
        backgroundColor: Colors.transparent,
        color: const Color(0xFF2D5EA2),
        buttonBackgroundColor: const Color(0xFF2D5EA2),
        height: 60,
        items: const [
          Icon(Icons.home, size: 30, color: Colors.white),
          Icon(Icons.receipt_long, size: 30, color: Colors.white),
          Icon(Icons.shopping_cart, size: 30, color: Colors.white),
        ],
        onTap: _onTap,
      ),
    );
  }
}
