import 'package:Delbites/home.dart';
import 'package:Delbites/keranjang.dart';
import 'package:Delbites/riwayat_pesanan.dart';
import 'package:curved_navigation_bar/curved_navigation_bar.dart';
import 'package:flutter/material.dart';

class MainScreen extends StatefulWidget {
  // --- UBAH BAGIAN INI ---
  // Tambahkan initialIndex pada constructor agar bisa ditentukan dari luar.
  final int initialIndex;

  const MainScreen({Key? key, this.initialIndex = 0}) : super(key: key);

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  late int _currentIndex;
  late PageController _pageController;

  // ID Pelanggan ini sebaiknya didapatkan dari proses login/SharedPreferences
  static const int _placeholderIdPelanggan = 1;

  final List<Widget> _pages = [
    const HomePage(),
    const RiwayatPesananPage(),
    const KeranjangPage(idPelanggan: _placeholderIdPelanggan),
  ];

  // --- UBAH BAGIAN INI ---
  @override
  void initState() {
    super.initState();
    // Atur index dan controller berdasarkan nilai yang diterima dari widget.
    _currentIndex = widget.initialIndex;
    _pageController = PageController(initialPage: _currentIndex);
  }

  void _onTap(int index) {
    setState(() => _currentIndex = index);
    _pageController.animateToPage(
      index,
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
    );
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(),
        children: _pages,
        onPageChanged: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
      bottomNavigationBar: CurvedNavigationBar(
        index: _currentIndex,
        backgroundColor: Colors.transparent,
        color: const Color(0xFF2D5EA2),
        buttonBackgroundColor: const Color(0xFF2D5EA2),
        height: 60,
        items: const <Widget>[
          Icon(Icons.home, size: 30, color: Colors.white),
          Icon(Icons.receipt_long, size: 30, color: Colors.white),
          Icon(Icons.shopping_cart, size: 30, color: Colors.white),
        ],
        onTap: _onTap,
      ),
    );
  }
}
