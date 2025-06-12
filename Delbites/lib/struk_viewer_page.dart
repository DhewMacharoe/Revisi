import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

// Halaman baru untuk menampilkan struk dalam format Dart/Flutter
class StrukViewerPage extends StatelessWidget {
  final Map<String, dynamic> order;

  const StrukViewerPage({Key? key, required this.order}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Helper untuk format Rupiah
    final currencyFormatter =
        NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    // Helper untuk format tanggal dan waktu
    final dateTimeFormatter = DateFormat('dd/MM/yyyy HH:mm', 'id_ID');
    final orderDate = DateTime.parse(order['created_at']);

    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Struk Pesanan #${order['id']}',
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // Header Struk
            const Text(
              'DELBITES',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 5),
            const Text(
              'Jl. Sisingamangaraja, Desa Sitoluama\nKec. Laguboti, Kab. Tobasa, Sumatera Utara',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12),
            ),
            const Text('Telp: (+62) 813 6091 2900', style: TextStyle(fontSize: 12)),
            const Text('A/N Hermanto S Sinaga', style: TextStyle(fontSize: 12)),
            const SizedBox(height: 8),
            const Text('================================', style: TextStyle(fontSize: 12)),
            const Text('STRUK PESANAN', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            
            // Detail Informasi Pesanan
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('No: #ORD-${order['id'].toString().padLeft(3, '0')}', style: const TextStyle(fontSize: 12)),
                Text(dateTimeFormatter.format(orderDate), style: const TextStyle(fontSize: 12)),
              ],
            ),
            const Row(
               mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                 Text('Kasir: Admin', style: TextStyle(fontSize: 12)),
              ],
            ),
            const SizedBox(height: 5),
            const Text('================================', style: TextStyle(fontSize: 12)),
            
            // Informasi Pelanggan dan Pembayaran
            Align(
              alignment: Alignment.centerLeft,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                   Text('Pelanggan: ${order['pelanggan']?['nama'] ?? 'N/A'}', style: const TextStyle(fontSize: 12)),
                   Text('Pembayaran: ${(order['metode_pembayaran'] as String).toUpperCase()}', style: const TextStyle(fontSize: 12)),
                ],
              ),
            ),
             const SizedBox(height: 5),
            const Text('- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ', style: TextStyle(fontSize: 12)),
            const SizedBox(height: 10),

            // Tabel Item Pesanan
            _buildItemTable(order['detail_pemesanan'], currencyFormatter),
            
             const SizedBox(height: 5),
            const Text('- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ', style: TextStyle(fontSize: 12)),
            
            // Total
             const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('TOTAL', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                Text(
                  currencyFormatter.format(double.tryParse(order['total_harga'].toString()) ?? 0),
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
              ],
            ),
             const SizedBox(height: 5),
            const Text('- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ', style: TextStyle(fontSize: 12)),
            
            // Footer
            const SizedBox(height: 20),
            const Text('Terima kasih atas kunjungan Anda!', style: TextStyle(fontSize: 12)),
            const Text('© Delbites 2025', style: TextStyle(fontSize: 10)),
          ],
        ),
      ),
    );
  }

  // Widget helper untuk membuat tabel item
  Widget _buildItemTable(List<dynamic> details, NumberFormat formatter) {
    return Column(
      children: [
        // Header Tabel
        const Row(
          children: [
            Expanded(flex: 4, child: Text('Item', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12))),
            Expanded(flex: 1, child: Text('Qty', textAlign: TextAlign.right, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12))),
            Expanded(flex: 3, child: Text('Harga', textAlign: TextAlign.right, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12))),
            Expanded(flex: 3, child: Text('Subtotal', textAlign: TextAlign.right, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12))),
          ],
        ),
        const Divider(),
        // List Item
        ...details.map((item) {
          final harga = double.tryParse(item['harga_satuan'].toString()) ?? 0;
          final subtotal = double.tryParse(item['subtotal'].toString()) ?? 0;
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 2.0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(flex: 4, child: Text(item['menu']?['nama_menu'] ?? 'N/A', style: const TextStyle(fontSize: 12))),
                Expanded(flex: 1, child: Text(item['jumlah'].toString(), textAlign: TextAlign.right, style: const TextStyle(fontSize: 12))),
                Expanded(flex: 3, child: Text(formatter.format(harga), textAlign: TextAlign.right, style: const TextStyle(fontSize: 12))),
                Expanded(flex: 3, child: Text(formatter.format(subtotal), textAlign: TextAlign.right, style: const TextStyle(fontSize: 12))),
              ],
            ),
          );
        }).toList(),
      ],
    );
  }
}
