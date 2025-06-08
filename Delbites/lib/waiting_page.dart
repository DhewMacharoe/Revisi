import 'dart:async';

import 'package:Delbites/main_screen.dart'; // Import MainScreen
import 'package:flutter/material.dart';

class WaitingPage extends StatefulWidget {
  final List<Map<String, dynamic>> orders;

  const WaitingPage({Key? key, required this.orders}) : super(key: key);

  @override
  State<WaitingPage> createState() => _WaitingPageState();
}

class _WaitingPageState extends State<WaitingPage> {
  int _counterBack = 5;
  late Timer _timerBack;

  // --- UBAH BAGIAN INI ---
  // Ubah timer dari 300 (5 menit) menjadi 900 (15 menit)
  int _counterCancel = 900;
  late Timer _timerCancel;
  
  bool _isCanceled = false;

  @override
  void initState() {
    super.initState();
    _startCountdown();
    _startCancelCountdown();
  }

  @override
  void dispose() {
    _timerBack.cancel();
    _timerCancel.cancel();
    super.dispose();
  }

  void _startCountdown() {
    _timerBack = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      if (_counterBack > 1) {
        setState(() {
          _counterBack--;
        });
      } else {
        timer.cancel();
        _goToHistoryPage();
      }
    });
  }
  
  void _startCancelCountdown() {
    _timerCancel = Timer.periodic(const Duration(seconds: 1), (timer) {
       if (!mounted) {
        timer.cancel();
        return;
      }
      if (_counterCancel > 0) {
        setState(() {
          _counterCancel--;
        });
      } else {
        timer.cancel();
        if (!_isCanceled) {
          _cancelOrder();
        }
      }
    });
  }

  void _goToHistoryPage() {
    const int riwayatIndex = 1;

    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(
        builder: (context) => const MainScreen(initialIndex: riwayatIndex),
      ),
      (route) => false,
    );
  }

  void _cancelOrder() {
    setState(() {
      _isCanceled = true;
    });
    // Di sini tidak perlu mengirim request pembatalan ke API,
    // karena server sudah menanganinya secara otomatis.
    // Kita hanya perlu mengarahkan pengguna.
  }

  void _goToHomePage() {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(
        builder: (context) => const MainScreen(initialIndex: 0),
      ),
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    String cancelMinutes = (_counterCancel ~/ 60).toString().padLeft(2, '0');
    String cancelSeconds = (_counterCancel % 60).toString().padLeft(2, '0');

    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              _isCanceled ? Icons.cancel_outlined : Icons.hourglass_empty,
              size: 120,
              color: _isCanceled ? Colors.red.shade700 : Colors.black,
            ),
            const SizedBox(height: 20),
            Text(
              _isCanceled ? "Pesanan Dibatalkan" : "Mohon Menunggu",
              style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 10),
            Text(
              _isCanceled
                  ? "Maaf, pesanan Anda telah dibatalkan karena tidak dibayar dalam 15 menit."
                  : "Silakan bayar di kasir langsung ya teman Del. Pesanan Anda sedang kami siapkan.",
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 16, color: Colors.grey.shade700),
            ),
            const SizedBox(height: 20),
            if (!_isCanceled)
              Text(
                "Mengarahkan ke Riwayat Pesanan dalam $_counterBack detik...",
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 16, color: Colors.black54),
              ),
            if (!_isCanceled) const SizedBox(height: 10),
            if (!_isCanceled)
              Text(
                "Pesanan akan batal otomatis dalam $cancelMinutes:$cancelSeconds",
                style: const TextStyle(fontSize: 14, color: Colors.red),
              ),
            const SizedBox(height: 40),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _goToHomePage,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2D5EA2),
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: const Text(
                  "Kembali ke Menu Utama",
                  style: TextStyle(color: Colors.white, fontSize: 16),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}