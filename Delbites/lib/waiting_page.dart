import 'dart:async';

import 'package:Delbites/riwayat_pesanan.dart';
import 'package:flutter/material.dart';

class WaitingPage extends StatefulWidget {
  final List<Map<String, dynamic>> orders;

  const WaitingPage({Key? key, required this.orders}) : super(key: key);

  @override
  State<WaitingPage> createState() => _WaitingPageState();
}

class _WaitingPageState extends State<WaitingPage> {
  int _counterBack = 5;
  int _counterCancel = 300;
  late Timer _timerBack;
  late Timer _timerCancel;
  bool _isCanceled = false;

  @override
  void initState() {
    super.initState();
    _startCountdown();
    _startCancelCountdown();
  }

  void _startCountdown() {
    _timerBack = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_counterBack > 1) {
        setState(() {
          _counterBack--;
        });
      } else {
        _timerBack.cancel();
        _confirmOrder();
      }
    });
  }

  void _startCancelCountdown() {
    _timerCancel = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_counterCancel > 1) {
        setState(() {
          _counterCancel--;
        });
      } else {
        _timerCancel.cancel();
        _cancelOrder();
      }
    });
  }

  void _confirmOrder() {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (context) => const RiwayatPesananPage(),
      ),
    );
  }

  void _cancelOrder() {
    setState(() {
      _isCanceled = true;
    });
    _goBackToMain();
  }

  void _goBackToMain() {
    Navigator.of(context).popUntil((route) => route.isFirst);
  }

  @override
  void dispose() {
    _timerBack.cancel();
    _timerCancel.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.hourglass_empty,
              size: 120,
              color: Colors.black,
            ),
            const SizedBox(height: 20),
            Text(
              _isCanceled ? "Pesanan Dibatalkan" : "Mohon Menunggu",
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              _isCanceled
                  ? "Maaf, pesanan Anda telah dibatalkan karena tidak dibayar dalam 5 menit."
                  : "Silahkan bayar di kasir langsung ya teman Del",
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            if (!_isCanceled)
              const Text(
                "Kami akan segera mengonfirmasi statusnya.\nTerima kasih telah memesan! 😊",
                textAlign: TextAlign.center,
              ),
            const SizedBox(height: 20),
            if (!_isCanceled)
              Center(
                child: Text(
                  "Kembali ke halaman utama dalam $_counterBack detik...",
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Colors.red,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            if (!_isCanceled) const SizedBox(height: 10),
            // if (!_isCanceled)
            //   Center(
            //     child: Text(
            //       "Pesanan akan dibatalkan dalam ${(_counterCancel ~/ 60)}:${(_counterCancel % 60).toString().padLeft(2, '0')} menit...",
            //       style: const TextStyle(
            //         fontSize: 16,
            //         fontWeight: FontWeight.bold,
            //         color: Colors.red,
            //       ),
            //       textAlign: TextAlign.center,
            //     ),
            //   ),
            const SizedBox(height: 40),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _goBackToMain,
                style: ElevatedButton.styleFrom(
                  backgroundColor:
                      _isCanceled ? Colors.grey : const Color(0xFF2D5EA2),
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: Text(
                  _isCanceled ? "Kembali ke Menu" : "Kembali Sekarang",
                  style: const TextStyle(color: Colors.white, fontSize: 16),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
