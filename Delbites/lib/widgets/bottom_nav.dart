import 'package:Delbites/home.dart';
import 'package:Delbites/riwayat_pesanan.dart';
import 'package:curved_navigation_bar/curved_navigation_bar.dart';
import 'package:flutter/material.dart';

// Halaman Placeholder untuk Keranjang
class KeranjangPage extends StatelessWidget {
  const KeranjangPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Keranjang Anda'),
        backgroundColor: const Color(0xFF2D5EA2),
      ),
      body: const Center(
        child: Text(
          'Halaman Keranjang',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}

class BottomNavBar extends StatefulWidget {
  final int currentIndex;

  const BottomNavBar({Key? key, required this.currentIndex}) : super(key: key);

  @override
  _BottomNavBarState createState() => _BottomNavBarState();
}

class _BottomNavBarState extends State<BottomNavBar> {
  // Fungsi Navigasi
  void _navigateTo(int index, BuildContext context) {
    if (index == widget.currentIndex) return;

    Widget targetPage;
    switch (index) {
      case 0:
        targetPage = const HomePage();
        break;
      case 1:
        targetPage = const RiwayatPesananPage();
        break;
      case 2:
        targetPage = const KeranjangPage();
        break;
      default:
        return;
    }

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => targetPage),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Struktur Curved Navigation Bar
    return CurvedNavigationBar(
      index: widget.currentIndex,
      backgroundColor: Colors.transparent,
      color: const Color(0xFF2D5EA2),
      buttonBackgroundColor: const Color(0xFF2D5EA2),
      height: 60,
      // Item Navigasi (sudah ada 3 ikon)
      items: const [
        Icon(Icons.home, size: 30, color: Colors.white), // Ikon 1: Home
        Icon(Icons.history,
            size: 30, color: Colors.white), // Ikon 2: Riwayat Pesanan
        Icon(Icons.shopping_cart,
            size: 30, color: Colors.white), // Ikon 3: Keranjang
      ],
      // Aksi Saat Item Diketuk
      onTap: (index) => _navigateTo(index, context),
    );
  }
}
