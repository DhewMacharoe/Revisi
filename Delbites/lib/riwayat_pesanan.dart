import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

Future<int?> getPelangganId() async {
  final prefs = await SharedPreferences.getInstance();
  return prefs.getInt('id_pelanggan'); // Pastikan key sesuai dengan penyimpanan
}

class RiwayatPesananPage extends StatefulWidget {
  const RiwayatPesananPage({Key? key}) : super(key: key);

  @override
  _RiwayatPesananPageState createState() => _RiwayatPesananPageState();
}

class _RiwayatPesananPageState extends State<RiwayatPesananPage> {
  String selectedStatus = "menunggu"; // Sesuaikan dengan nilai di database
  List<dynamic> orders = [];
  bool isLoading = false;
  int currentPage = 1;
  bool hasNextPage = true;

  Map<int, double> tempRatings = {};
  Map<int, bool> submittedRatings = {};

  // Daftar status dari database
  final List<String> statusList = [
    "menunggu",
    "diproses",
    // "dikirim",
    "selesai",
    "dibatalkan"
  ];

  // Warna untuk masing-masing status
  final Map<String, Color> statusColors = {
    "menunggu": Colors.amber,
    "diproses": Colors.blue,
    // "dikirim": Colors.indigo,
    "selesai": Colors.green,
    "dibatalkan": Colors.red,
  };

  @override
  void initState() {
    super.initState();
    fetchOrders();
  }

  Future<void> fetchOrders() async {
    setState(() => isLoading = true);

    try {
      final pelangganId = await getPelangganId();
      if (pelangganId == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text(
                  'ID Pelanggan tidak ditemukan, silahkan daftar terlebih dahulu')),
        );
        return;
      }
      final uri = Uri.parse(
              'http://127.0.0.1:8000/api/pemesanan/pelanggan/$pelangganId')
          .replace(queryParameters: {
        'status': selectedStatus,
        'page': currentPage.toString(),
      });

      final response = await http.get(uri);

      print('URL: ${uri.toString()}'); // Debug URL
      print('Response Status: ${response.statusCode}'); // Debug status code
      print('Response Body: ${response.body}'); // Debug response body

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          orders = List<Map<String, dynamic>>.from(data['data'] ?? []);
          currentPage = data['current_page'] ?? 1;
          totalPages = data['last_page'] ?? 1;
          hasNextPage = currentPage < totalPages;
        });
      } else {
        throw Exception('Gagal memuat pesanan. Status: ${response.statusCode}');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
      print('Error details: $e');
    } finally {
      setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Riwayat Pesanan',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2D5EA2),
      ),
      body: Column(
        children: [
          _buildStatusFilter(),
          isLoading
              ? const Center(child: CircularProgressIndicator())
              : Expanded(child: _buildOrderList()),
          _buildPaginationControls(),
        ],
      ),
      // bottomNavigationBar: const BottomNavBar(currentIndex: 1),
    );
  }

  Widget _buildStatusFilter() {
    return Container(
      height: 60, // Berikan tinggi yang jelas
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: statusList.map((status) {
            return Container(
              margin: const EdgeInsets.symmetric(horizontal: 4),
              child: ChoiceChip(
                label: Text(
                  status.toUpperCase(),
                  style: TextStyle(
                    color: selectedStatus == status
                        ? Colors.white
                        : statusColors[status],
                  ),
                ),
                selected: selectedStatus == status,
                selectedColor: statusColors[status],
                backgroundColor: Colors.grey[200],
                onSelected: (selected) {
                  if (selected) {
                    setState(() {
                      selectedStatus = status;
                      fetchOrders();
                    });
                  }
                },
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildOrderList() {
    final filteredOrders = orders
        .where((order) =>
            order['status'].toString().toLowerCase() == selectedStatus)
        .toList();

    if (filteredOrders.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Text(
            'Tidak ada pesanan dengan status ini',
            style: TextStyle(fontSize: 16, color: Colors.grey[600]),
          ),
        ),
      );
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const AlwaysScrollableScrollPhysics(),
      itemCount: filteredOrders.length,
      itemBuilder: (context, index) {
        final order = filteredOrders[index];
        final details = order['detail_pemesanan'] as List<dynamic>? ?? [];

        return Container(
          margin: const EdgeInsets.all(8.0),
          child: Material(
            borderRadius: BorderRadius.circular(8),
            elevation: 2,
            child: InkWell(
              borderRadius: BorderRadius.circular(8),
              onTap: () {
                // Tambahkan aksi ketika card diklik
                _showOrderDetail(context, order);
              },
              child: ExpansionTile(
                tilePadding: const EdgeInsets.symmetric(horizontal: 16),
                leading: CircleAvatar(
                  backgroundColor: statusColors[order['status']],
                  child: Text(
                    '${index + 1}',
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
                title: Text(
                  'Pesanan #${order['id']}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 4),
                    Text(
                      'Total: Rp ${order['total_harga']}',
                      style: const TextStyle(fontSize: 14),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      order['status'].toString().toUpperCase(),
                      style: TextStyle(
                        color: statusColors[order['status']],
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Tanggal: ${order['waktu_pemesanan']}'),
                        Text('Metode: ${order['metode_pembayaran']}'),
                        const SizedBox(height: 16),
                        const Text(
                          'Detail Pesanan:',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        const Divider(),
                        ...details.map((detail) {
                          return Row(
                            children: [
                              Expanded(
                                flex: 3,
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      detail['menu']?['nama_menu']
                                              ?.toString() ??
                                          'Unknown',
                                      style: const TextStyle(fontSize: 14),
                                    ),
                                    if (detail['suhu'] != null)
                                      Text(
                                        'Suhu: ${detail['suhu']}',
                                        style: const TextStyle(
                                            fontSize: 12, color: Colors.grey),
                                      ),
                                  ],
                                ),
                              ),
                              Expanded(
                                flex: 1,
                                child: Text('x${detail['jumlah']}'),
                              ),
                              Expanded(
                                flex: 2,
                                child: Text(
                                  'Rp ${detail['harga_satuan'] * detail['jumlah']}',
                                  textAlign: TextAlign.end,
                                ),
                              ),
                            ],
                          );
                        }),
                        if (order['status'] == 'selesai') ...[
                          const SizedBox(height: 16),
                          const Text('Beri Rating Anda:'),
                          ...details.map((detail) {
                            final menuId = detail['menu']?['id'];
                            final namaMenu =
                                detail['menu']?['nama_menu'] ?? 'Menu';
                            final detailId = detail['id'];
                            final ratingValue = double.tryParse(
                                    detail['rating']?.toString() ?? '') ??
                                0.0;
                            final alreadyRated =
                                submittedRatings[detailId] ?? ratingValue > 0;

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(namaMenu),
                                Row(
                                  children: [
                                    RatingBar.builder(
                                      initialRating:
                                          tempRatings[detailId] ?? ratingValue,
                                      minRating: 1,
                                      direction: Axis.horizontal,
                                      itemCount: 5,
                                      itemSize: 20,
                                      ignoreGestures:
                                          alreadyRated, // Disable jika sudah rated
                                      itemBuilder: (context, _) => const Icon(
                                          Icons.star,
                                          color: Colors.amber),
                                      onRatingUpdate: (rating) {
                                        setState(() {
                                          tempRatings[detailId] = rating;
                                        });
                                      },
                                    ),
                                    if (!alreadyRated &&
                                        tempRatings.containsKey(detailId))
                                      TextButton(
                                        onPressed: () async {
                                          final rating = tempRatings[detailId]!;
                                          try {
                                            final res = await http.put(
                                              Uri.parse(
                                                  'http://127.0.0.1:8000/api/detail-pemesanan/$detailId/rating'),
                                              headers: {
                                                'Content-Type':
                                                    'application/json'
                                              },
                                              body: jsonEncode(
                                                  {'rating': rating}),
                                            );

                                            if (res.statusCode == 200) {
                                              setState(() {
                                                submittedRatings[detailId] =
                                                    true;
                                              });
                                              ScaffoldMessenger.of(context)
                                                  .showSnackBar(
                                                const SnackBar(
                                                    content: Text(
                                                        'Rating berhasil dikirim')),
                                              );
                                            } else {
                                              ScaffoldMessenger.of(context)
                                                  .showSnackBar(
                                                SnackBar(
                                                    content: Text(
                                                        'Gagal mengirim rating: ${res.body}')),
                                              );
                                            }
                                          } catch (e) {
                                            ScaffoldMessenger.of(context)
                                                .showSnackBar(
                                              SnackBar(
                                                  content:
                                                      Text('Error rating: $e')),
                                            );
                                          }
                                        },
                                        child: const Text('Kirim'),
                                      ),
                                  ],
                                ),
                                const Divider(),
                              ],
                            );
                          }).toList()
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  void _changePage(int newPage) {
    setState(() {
      currentPage = newPage;
    });
    fetchOrders();
  }

  Widget _buildPaginationControls() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        TextButton(
          onPressed:
              currentPage > 1 ? () => _changePage(currentPage - 1) : null,
          child: const Text('<'),
        ),
        Text('Halaman $currentPage'),
        TextButton(
          onPressed: hasNextPage ? () => _changePage(currentPage + 1) : null,
          child: const Text('>'),
        ),
      ],
    );
  }

  void _showOrderDetail(BuildContext context, Map<String, dynamic> order) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text('Detail Pesanan #${order['id']}'),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('Status: ${order['status']}'),
                Text('Total: Rp ${order['total_harga']}'),
                Text('Metode Bayar: ${order['metode_pembayaran']}'),
                const SizedBox(height: 16),
                const Text(
                  'Item Pesanan:',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                ...(order['detail_pemesanan'] as List)
                    .map((detail) => ListTile(
                          title:
                              Text(detail['menu']?['nama_menu'] ?? 'Unknown'),
                          subtitle: Text('x${detail['jumlah']}'),
                          trailing: Text('Rp ${detail['harga_satuan']}'),
                        ))
                    .toList(),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Tutup'),
            ),
          ],
        );
      },
    );
  }

  int totalPages = 1; // Tambahkan ini ke atas State

  Widget _buildNumberedPaginationControls() {
    List<Widget> pageButtons = List.generate(totalPages, (index) {
      int pageNumber = index + 1;
      return InkWell(
        onTap: () {
          setState(() {
            currentPage = pageNumber;
          });
          fetchOrders();
        },
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 4),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: currentPage == pageNumber ? Colors.blue : Colors.transparent,
            borderRadius: BorderRadius.circular(4),
          ),
          child: Text(
            '$pageNumber',
            style: TextStyle(
              color: currentPage == pageNumber ? Colors.white : Colors.blue,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      );
    });

    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        IconButton(
          icon: const Icon(Icons.chevron_left),
          onPressed: currentPage > 1
              ? () {
                  setState(() {
                    currentPage--;
                  });
                  fetchOrders();
                }
              : null,
        ),
        ...pageButtons,
        IconButton(
          icon: const Icon(Icons.chevron_right),
          onPressed: currentPage < totalPages
              ? () {
                  setState(() {
                    currentPage++;
                  });
                  fetchOrders();
                }
              : null,
        ),
      ],
    );
  }
}
