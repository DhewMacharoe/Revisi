import 'package:Delbites/main_screen.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:webview_flutter/webview_flutter.dart';

// const String baseUrl = 'http://127.0.0.1:8000';
const String baseUrl = 'http://127.0.0.1:8000:8000';

class MidtransPaymentPage extends StatefulWidget {
  final String redirectUrl;
  final String orderId;
  final List<Map<String, dynamic>> pesanan;
  final int idPelanggan;
  final int totalHarga;

  const MidtransPaymentPage({
    Key? key,
    required this.redirectUrl,
    required this.orderId,
    required this.pesanan,
    required this.idPelanggan,
    required this.totalHarga,
  }) : super(key: key);

  @override
  State<MidtransPaymentPage> createState() => _MidtransPaymentPageState();
}

class _MidtransPaymentPageState extends State<MidtransPaymentPage> {
  late WebViewController _controller;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(onPageStarted: (String url) {
          setState(() {
            isLoading = true;
          });
        }, onPageFinished: (String url) {
          setState(() {
            isLoading = false;
          });

          // Cek jika transaksi selesai berdasarkan URL Midtrans
          if (url.contains('transaction_status=settlement') ||
              url.contains('transaction_status=capture') ||
              url.contains('status_code=200')) {
            _handlePaymentSuccess();
          } else if (url.contains('transaction_status=deny') ||
              url.contains('transaction_status=cancel') ||
              url.contains('transaction_status=expire') ||
              url.contains('status_code=202')) {
            _handlePaymentFailure();
          } else if (url.contains('example.com')) {
            // Gantikan link ini dengan navigasi manual
            Navigator.pushAndRemoveUntil(
              context,
              MaterialPageRoute(builder: (_) => const MainScreen()),
              (route) => false,
            );
          }

          // Cegah redirect ke example.com
          if (url.contains('order_id=')) {
            _handlePaymentSuccess(); // Asumsikan sukses, atau panggil check status manual
          }
        }),
      )
      ..loadRequest(Uri.parse(widget.redirectUrl));
  }

  Future<void> _handlePaymentSuccess() async {
    final prefs = await SharedPreferences.getInstance();

    // Bersihkan data midtrans
    await prefs.remove('midtrans_order_id');
    await prefs.remove('midtrans_redirect_url');

    try {
      // Hapus keranjang
      await http.delete(
        Uri.parse('$baseUrl/api/keranjang/pelanggan/${widget.idPelanggan}'),
        headers: {'Content-Type': 'application/json'},
      );
    } catch (_) {
      // Abaikan error keranjang
    }

    // Kembali ke Home
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => const MainScreen(),
        settings: const RouteSettings(arguments: 0), // ke tab Home
      ),
    );
  }

  Future<void> _handlePaymentFailure() async {
    final prefs = await SharedPreferences.getInstance();

    await prefs.remove('midtrans_order_id');
    await prefs.remove('midtrans_redirect_url');

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Pembayaran dibatalkan atau gagal')),
    );

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => const MainScreen(),
        settings: const RouteSettings(arguments: 0),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pembayaran', style: TextStyle(color: Colors.white)),
        backgroundColor: const Color(0xFF2D5EA2),
        leading: IconButton(
          icon: const Icon(Icons.close, color: Colors.white),
          onPressed: () {
            showDialog(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Batalkan Pembayaran'),
                content: const Text('Yakin ingin membatalkan pembayaran ini?'),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Tidak'),
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.pop(context); // Tutup dialog
                      Navigator.pop(context); // Tutup halaman Midtrans
                    },
                    child: const Text('Ya'),
                  ),
                ],
              ),
            );
          },
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (isLoading) const Center(child: CircularProgressIndicator()),
        ],
      ),
    );
  }
}
