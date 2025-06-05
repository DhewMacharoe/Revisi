import 'package:Delbites/menu_detail.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
// import 'package:uuid/uuid.dart'; // Tidak lagi diperlukan

const String baseUrl = 'https://delbites.d4trpl-itdel.id';

class MenuCard extends StatelessWidget {
  final Map<String, String> item;

  const MenuCard({Key? key, required this.item}) : super(key: key);

  // Fungsi untuk memeriksa data pelanggan dan menavigasi ke detail menu
  Future<void> checkAndNavigate(
      BuildContext context, Map<String, String> item) async {
    // Navigasi langsung ke MenuDetail.
    // Logika pengecekan dan pengisian data pelanggan kini ditangani di MenuDetail saat addToCart.
    _navigateToMenuDetail(context, item);
  }

  // Fungsi format harga
  String formatPrice(String rawPrice) {
    final int price = int.tryParse(
            rawPrice.replaceAll('.', '').replaceAll('Rp', '').trim()) ??
        0;
    return NumberFormat.decimalPattern('id').format(price);
  }

  // Fungsi getDeviceId dihapus dari sini karena sudah dipindahkan ke isi_data.dart
  // Future<String> getDeviceId() async { ... }

  // Fungsi untuk menavigasi ke halaman detail menu
  void _navigateToMenuDetail(BuildContext context, Map<String, String> item) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => MenuDetail(
          name: item['name']!,
          price: item['price']!,
          imageUrl: "$baseUrl/storage/${item['image']!}",
          menuId: int.parse(item['id']!),
          rating: item['rating'] ?? '0.0',
          kategori: item['kategori']!,
          deskripsi: item['deskripsi'] ?? '',
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bool isOutOfStock = int.parse(item['stok']!) == 0;

    return GestureDetector(
      onTap: () {
        if (!isOutOfStock) {
          // Panggil checkAndNavigate yang sekarang langsung menavigasi
          checkAndNavigate(context, item);
        }
      },
      child: Opacity(
        opacity: isOutOfStock ? 0.5 : 1.0,
        child: Card(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          elevation: 4,
          child: Stack(
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  ClipRRect(
                    borderRadius:
                        const BorderRadius.vertical(top: Radius.circular(16)),
                    child: Image.network(
                      'https://delbites.d4trpl-itdel.id/storage/${item['image']}',
                      height: 120,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      loadingBuilder: (context, child, loadingProgress) {
                        return loadingProgress == null
                            ? child
                            : const Center(child: CircularProgressIndicator());
                      },
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          height: 120,
                          color: Colors.grey[300],
                          child: const Center(
                            child: Icon(Icons.broken_image, size: 40),
                          ),
                        );
                      },
                    ),
                  ),
                  Padding(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item['name']!,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Rp ${formatPrice(item['price']!)}',
                          style: const TextStyle(color: Colors.black87),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              if (isOutOfStock)
                Positioned(
                  top: 8,
                  right: 8,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: const BoxDecoration(
                      color: Colors.red,
                      borderRadius: BorderRadius.all(Radius.circular(12)),
                    ),
                    child: const Text(
                      'Habis',
                      style: TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
