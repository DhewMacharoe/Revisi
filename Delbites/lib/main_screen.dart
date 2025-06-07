import 'package:Delbites/home.dart';
import 'package:Delbites/keranjang.dart'; 
import 'package:Delbites/riwayat_pesanan.dart';
import 'package:curved_navigation_bar/curved_navigation_bar.dart';
import 'package:flutter/material.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({Key? key}) : super(key: key); // Current constructor

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;
  final PageController _pageController = PageController();
  // This might come from user authentication, shared preferences, or passed via constructor.
  static const int _placeholderIdPelanggan = 1;

  final List<Widget> _pages = [
    const HomePage(),
    const RiwayatPesananPage(),
    const KeranjangPage(
        idPelanggan: _placeholderIdPelanggan), // Added KeranjangPage
  ];

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
        physics:
            const NeverScrollableScrollPhysics(), // To disable swiping between pages
        children: _pages,
        onPageChanged: (index) {
          // Optional: if you want to update index if PageView somehow changes
          setState(() {
            _currentIndex = index;
          });
        },
      ),
      bottomNavigationBar: CurvedNavigationBar(
        index: _currentIndex,
        backgroundColor: Colors.transparent, // Or your page background color
        color: const Color(0xFF2D5EA2), // Navbar color
        buttonBackgroundColor: const Color(0xFF2D5EA2), // Icon background color
        height: 60,
        items: const <Widget>[
          Icon(Icons.home, size: 30, color: Colors.white),
          Icon(Icons.receipt_long,
              size: 30,
              color: Colors.white), // Changed icon for Riwayat (History)
          Icon(Icons.shopping_cart,
              size: 30, color: Colors.white), // Added icon for Keranjang (Cart)
        ],
        onTap: _onTap,
      ),
    );
  }
}
