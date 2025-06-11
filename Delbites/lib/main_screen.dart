// Salin dan ganti seluruh isi file main_screen.dart Anda dengan ini.

import 'dart:async';
import 'dart:convert';

import 'package:Delbites/home.dart';
import 'package:Delbites/keranjang.dart';
import 'package:Delbites/riwayat_pesanan.dart';
import 'package:curved_navigation_bar/curved_navigation_bar.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

Future<int?> getPelangganId() async {
  final prefs = await SharedPreferences.getInstance();
  const String keyUntukId = 'id_pelanggan';
  final dynamic idValue = prefs.get(keyUntukId);

  if (idValue == null) return null;
  if (idValue is int) return idValue;
  if (idValue is String) return int.tryParse(idValue);
  return null;
}

class MainScreen extends StatefulWidget {
  final int initialIndex;
  const MainScreen({Key? key, this.initialIndex = 0}) : super(key: key);

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  late int _currentIndex;
  late PageController _pageController;

  int _cartItemCount = 0;
  int _historyNotificationCount = 0;
  Timer? _notificationTimer;

  // [CHANGED] Deklarasikan _pages di sini, tapi inisialisasi di initState
  late final List<Widget> _pages;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _pageController = PageController(initialPage: _currentIndex);
    
    // [CHANGED] Inisialisasi _pages di sini agar bisa meneruskan fungsi
    _pages = [
      const HomePage(),
      // Berikan fungsi _fetchAllNotifications sebagai callback
      RiwayatPesananPage(onStateUpdated: _fetchAllNotifications),
      const KeranjangPage(),
    ];
    
    _fetchAllNotifications();
    _notificationTimer = Timer.periodic(const Duration(seconds: 15), (timer) {
      if (mounted) {
        _fetchAllNotifications();
      }
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    _notificationTimer?.cancel();
    super.dispose();
  }

  void _fetchAllNotifications() {
    _fetchCartItemCount();
    _fetchHistoryBadgeStatus();
  }

  Future<void> _fetchHistoryBadgeStatus() async {
    final idPelanggan = await getPelangganId();
    if (idPelanggan == null) {
      if (mounted && _historyNotificationCount != 0) {
        setState(() => _historyNotificationCount = 0);
      }
      return;
    }

    try {
      final url = Uri.parse(
          'http://127.0.0.1:8000/api/pemesanan/pelanggan/$idPelanggan/finished-ids');
      final response = await http.get(url);

      if (response.statusCode != 200) return;

      final serverData = json.decode(response.body);
      final serverIds = Set<String>.from(
          (serverData['order_ids'] as List).map((id) => id.toString()));

      final prefs = await SharedPreferences.getInstance();
      // Gunakan kunci yang sama persis seperti di riwayat_pesanan.dart
      const String readFinishedOrdersKey = 'read_finished_order_ids_v2';
      final localReadIds = Set<String>.from(
          prefs.getStringList(readFinishedOrdersKey) ?? []);

      final unreadIds = serverIds.difference(localReadIds);
      final int unreadCount = unreadIds.length;
      
      if (mounted && unreadCount != _historyNotificationCount) {
        setState(() {
          _historyNotificationCount = unreadCount;
        });
      }
    } catch (e) {
      // Error handling
    }
  }

  Future<void> _fetchCartItemCount() async {
    final idPelanggan = await getPelangganId();
    if (idPelanggan == null) {
      if (mounted && _cartItemCount != 0) {
        setState(() => _cartItemCount = 0);
      }
      return;
    }

    try {
      final url = Uri.parse(
          'http://127.0.0.1:8000/api/keranjang/pelanggan/$idPelanggan/count');
      final response = await http.get(url);
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (mounted) {
          setState(() {
            _cartItemCount = data['count'] ?? 0;
          });
        }
      } else {
        if (mounted) setState(() => _cartItemCount = 0);
      }
    } catch (e) {
      if (mounted) setState(() => _cartItemCount = 0);
    }
  }

  void _onTap(int index) {
    // Jika pengguna pindah DARI tab riwayat, kita refresh notifikasi
    // untuk memastikan lencana hilang jika sudah dibaca semua.
    if (_currentIndex == 1 && index != 1) {
      _fetchAllNotifications();
    }
    
    setState(() => _currentIndex = index);
    _pageController.animateToPage(
      index,
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
    );
  }

  Widget _buildHistoryIconWithBadge() {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        const Icon(Icons.receipt_long, size: 30, color: Colors.white),
        if (_historyNotificationCount > 0)
          Positioned(
            right: -6,
            top: -8,
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: BoxDecoration(
                color: Colors.red,
                borderRadius: BorderRadius.circular(10),
              ),
              constraints: const BoxConstraints(
                minWidth: 18,
                minHeight: 18,
              ),
              child: Text(
                '$_historyNotificationCount',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildCartIconWithBadge() {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        const Icon(Icons.shopping_cart, size: 30, color: Colors.white),
        if (_cartItemCount > 0)
          Positioned(
            right: -6,
            top: -8,
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: BoxDecoration(
                color: Colors.red,
                borderRadius: BorderRadius.circular(10),
              ),
              constraints: const BoxConstraints(
                minWidth: 18,
                minHeight: 18,
              ),
              child: Text(
                '$_cartItemCount',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
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
        items: <Widget>[
          const Icon(Icons.home, size: 30, color: Colors.white),
          _buildHistoryIconWithBadge(),
          _buildCartIconWithBadge(),
        ],
        onTap: _onTap,
      ),
    );
  }
}