import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
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

class RiwayatPesananPage extends StatefulWidget {
  final VoidCallback? onStateUpdated;

  const RiwayatPesananPage({Key? key, this.onStateUpdated}) : super(key: key);

  @override
  _RiwayatPesananPageState createState() => _RiwayatPesananPageState();
}

class _RiwayatPesananPageState extends State<RiwayatPesananPage> {
  Timer? _timer;

  String selectedStatus = "menunggu";
  List<dynamic> orders = [];
  bool isLoading = true;
  int currentPage = 1;
  int totalPages = 1;
  bool hasNextPage = true;

  Map<int, double> tempRatings = {};
  Map<int, bool> submittedRatings = {};

  bool _hasNewFinishedOrders = false;
  Set<int> _unreadFinishedOrderIds = {};
  static const String _readFinishedOrdersKey = 'read_finished_order_ids_v2';

  final List<String> statusList = [
    "menunggu",
    "pembayaran",
    "diproses",
    "selesai",
    "dibatalkan"
  ];

  final Map<String, Color> statusColors = {
    "menunggu": Colors.amber.shade700,
    "pembayaran": Colors.cyan.shade600,
    "diproses": Colors.blue.shade700,
    "selesai": Colors.green.shade700,
    "dibatalkan": Colors.red.shade700,
  };

  final Map<String, String> statusDescriptions = {
    "menunggu": "Pesanan masih belum dibayarkan melalui kasir, silahkan bayar yaa...",
    "pembayaran": "Status sudah masuk di pembayaran melalui online namun masih tahap pengecekan.",
    "diproses": "Pesananmu sudah dibuat dan akan segera selesai.",
    "selesai": "Pesananmu sudah siap untuk diambil.",
    "dibatalkan": "Pesanan gagal dilanjutkan karena kesalahan ataupun tidak dibayarkan."
  };


  @override
  void initState() {
    super.initState();
    _checkNewFinishedOrders();
    fetchOrders();

    _timer = Timer.periodic(const Duration(seconds: 10), (timer) {
      if (mounted) {
        fetchOrders();
        _checkNewFinishedOrders();
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  // [DIUBAH] Fungsi ini sekarang menampilkan semua informasi status sekaligus
  void _showAllStatusHelpDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Informasi Status Pesanan'),
          content: SingleChildScrollView(
            child: ListBody(
              children: statusDescriptions.entries.map((entry) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 12.0),
                  child: RichText(
                    text: TextSpan(
                      style: DefaultTextStyle.of(context).style,
                      children: <TextSpan>[
                        TextSpan(
                          text: '${entry.key.toUpperCase()}\n',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: statusColors[entry.key] ?? Colors.black,
                            fontSize: 16,
                          ),
                        ),
                        TextSpan(text: entry.value, style: const TextStyle(height: 1.5)),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          actions: <Widget>[
            TextButton(
              child: const Text('Mengerti'),
              onPressed: () {
                Navigator.of(context).pop();
              },
            ),
          ],
        );
      },
    );
  }

  Future<void> _checkNewFinishedOrders() async {
    final idPelanggan = await getPelangganId();
    if (idPelanggan == null) return;

    try {
      final url = Uri.parse(
          'http://127.0.0.1:8000/api/pemesanan/pelanggan/$idPelanggan/finished-ids');
      final response = await http.get(url);
      if (response.statusCode != 200) return;

      final serverData = json.decode(response.body);
      final serverIds =
          (serverData['order_ids'] as List).map((id) => id as int).toSet();

      final prefs = await SharedPreferences.getInstance();
      final readIds =
          (prefs.getStringList(_readFinishedOrdersKey) ?? []).map(int.parse).toSet();

      final unreadIds = serverIds.difference(readIds);

      if (mounted) {
        setState(() {
          _unreadFinishedOrderIds = unreadIds;
          _hasNewFinishedOrders = unreadIds.isNotEmpty;
        });
      }
    } catch (e) {
      // Error handling
    }
  }

  Future<void> _markOrderAsRead(int orderId) async {
    if (!_unreadFinishedOrderIds.contains(orderId)) return;

    final wasLastUnread = _unreadFinishedOrderIds.length == 1;

    setState(() {
      _unreadFinishedOrderIds.remove(orderId);
    });

    final prefs = await SharedPreferences.getInstance();
    final readIds =
        (prefs.getStringList(_readFinishedOrdersKey) ?? []).toSet();
    readIds.add(orderId.toString());
    await prefs.setStringList(_readFinishedOrdersKey, readIds.toList());

    if (wasLastUnread) {
       widget.onStateUpdated?.call();
    }
  }


  Future<void> fetchOrders() async {
    final pelangganId = await getPelangganId();
    if (pelangganId == null) {
      if (!mounted) return;
      setState(() {
        isLoading = false;
        orders.clear();
      });
      return;
    }
    
    if (orders.isEmpty && mounted) {
      setState(() => isLoading = true);
    }

    try {
      final uri = Uri.parse(
              'http://127.0.0.1:8000/api/pemesanan/pelanggan/$pelangganId')
          .replace(queryParameters: {
        'status': selectedStatus,
        'page': currentPage.toString(),
      });

      final response = await http.get(uri);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (mounted) {
          final fetchedOrders =
              List<Map<String, dynamic>>.from(data['data'] ?? []);
          setState(() {
            orders = fetchedOrders;
            currentPage = data['current_page'] ?? 1;
            totalPages = data['last_page'] ?? 1;
            hasNextPage = currentPage < totalPages;
          });
        }
      } else {
        throw Exception('Gagal memuat pesanan. Status: ${response.statusCode}');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Terjadi kesalahan: ${e.toString()}')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => isLoading = false);
      }
    }
  }

  Future<void> _submitRating(int detailId, double rating) async {
    try {
      final res = await http.put(
        Uri.parse(
            'http://127.0.0.1:8000/api/detail-pemesanan/$detailId/rating'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: jsonEncode({'rating': rating}),
      );

      if (res.statusCode == 200) {
        setState(() {
          submittedRatings[detailId] = true;
          tempRatings.remove(detailId);
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Rating berhasil dikirim. Terima kasih!')),
        );
      } else {
        final error = jsonDecode(res.body);
        throw Exception(error['message'] ?? 'Gagal mengirim rating');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
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
        // [DIUBAH] Menambahkan tombol aksi di AppBar
        actions: [
          IconButton(
            icon: const Icon(Icons.help_outline, color: Colors.white),
            onPressed: _showAllStatusHelpDialog,
            tooltip: 'Bantuan Status Pesanan',
          ),
        ],
        automaticallyImplyLeading: false, 
      ),
      body: Column(
        children: [
          _buildStatusFilter(),
          Expanded(
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : _buildOrderList(),
          ),
          if (!isLoading && orders.isNotEmpty) _buildPaginationControls(),
        ],
      ),
    );
  }
  
  // [DIUBAH] Menghapus ikon bantuan dari setiap tombol
  Widget _buildStatusFilter() {
    return Container(
      height: 60,
      color: Colors.white,
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: statusList.length,
        itemBuilder: (context, index) {
          final status = statusList[index];
          final bool isSelesaiTabWithNotif =
              status == 'selesai' && _hasNewFinishedOrders;

          return Padding(
            padding: EdgeInsets.only(
                left: index == 0 ? 12.0 : 4.0,
                right: index == statusList.length - 1 ? 12.0 : 4.0),
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                ChoiceChip(
                  label: Text(
                    status.toUpperCase(),
                    style: TextStyle(
                      color: selectedStatus == status
                          ? Colors.white
                          : statusColors[status],
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  selected: selectedStatus == status,
                  selectedColor: statusColors[status],
                  backgroundColor: Colors.grey[200],
                  onSelected: (selected) {
                    if (selected) {
                      if (status == 'selesai') {
                        if (_hasNewFinishedOrders) {
                          setState(() {
                             _hasNewFinishedOrders = false;
                          });
                           widget.onStateUpdated?.call();
                        }
                      }
                      setState(() {
                        selectedStatus = status;
                        currentPage = 1;
                        orders.clear();
                      });
                      fetchOrders();
                    }
                  },
                ),
                if (isSelesaiTabWithNotif)
                  Positioned(
                    top: -2,
                    right: -2,
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: Colors.red,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildOrderList() {
    if (orders.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Text(
            'Tidak ada pesanan dengan status "$selectedStatus".',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 16, color: Colors.grey[600]),
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: fetchOrders,
      child: ListView.builder(
        padding: const EdgeInsets.all(8.0),
        itemCount: orders.length,
        itemBuilder: (context, index) {
          final order = orders[index];
          return _buildOrderCard(order);
        },
      ),
    );
  }
  
  Widget _buildOrderCard(Map<String, dynamic> order) {
    final details = order['detail_pemesanan'] as List<dynamic>? ?? [];
    final status = order['status'].toString();
    final orderId = order['id'] as int;
    final bool isNew = _unreadFinishedOrderIds.contains(orderId);

    return Card(
      elevation: 3,
      margin: const EdgeInsets.symmetric(vertical: 8.0),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ExpansionTile(
        onExpansionChanged: (isExpanded) {
          if (isExpanded && isNew) {
            _markOrderAsRead(orderId);
          }
        },
        tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: CircleAvatar(
          backgroundColor: statusColors[status] ?? Colors.grey,
          child: const Icon(Icons.receipt_long, color: Colors.white),
        ),
        title: Row(
          children: [
            Text(
              'Pesanan #${order['id']}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            if (isNew) ...[
              const SizedBox(width: 8),
              Chip(
                label: const Text('BARU'),
                labelStyle: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                backgroundColor: Colors.red,
                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 0),
                visualDensity: VisualDensity.compact,
              ),
            ]
          ],
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text('Total: Rp ${order['total_harga']}'),
            const SizedBox(height: 4),
            Text(
              status.toUpperCase(),
              style: TextStyle(
                color: statusColors[status],
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Divider(),
                Text('Tanggal: ${order['waktu_pemesanan']}',
                    style: TextStyle(color: Colors.grey.shade700)),
                Text('Metode: ${order['metode_pembayaran']}',
                    style: TextStyle(color: Colors.grey.shade700)),
                const SizedBox(height: 16),
                const Text('Detail Item:',
                    style:
                        TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                ...details.map((detail) => _buildOrderDetailItem(detail)),
                if (status == 'selesai') ...[
                  const SizedBox(height: 16),
                  const Text('Beri Rating Anda:',
                      style:
                          TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  ...details.map((detail) => _buildRatingItem(detail)),
                ]
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderDetailItem(Map<String, dynamic> detail) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Expanded(
            flex: 4,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(detail['menu']?['nama_menu']?.toString() ??
                    'Nama Menu Tidak Ada'),
                if (detail['suhu'] != null)
                  Text('Suhu: ${detail['suhu']}',
                      style: const TextStyle(fontSize: 12, color: Colors.grey)),
              ],
            ),
          ),
          Expanded(flex: 1, child: Text('x${detail['jumlah']}')),
          Expanded(
            flex: 2,
            child: Text('Rp ${detail['harga_satuan'] * detail['jumlah']}',
                textAlign: TextAlign.end),
          ),
        ],
      ),
    );
  }

  Widget _buildRatingItem(Map<String, dynamic> detail) {
    final detailId = detail['id'];
    final namaMenu = detail['menu']?['nama_menu'] ?? 'Menu';
    final ratingValue =
        double.tryParse(detail['rating']?.toString() ?? '0.0') ?? 0.0;
    final alreadyRated = submittedRatings[detailId] ?? ratingValue > 0;

    return Padding(
      padding: const EdgeInsets.only(top: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Divider(),
          Text(namaMenu),
          const SizedBox(height: 4),
          Row(
            children: [
              RatingBar.builder(
                initialRating: tempRatings[detailId] ?? ratingValue,
                minRating: 1,
                direction: Axis.horizontal,
                itemCount: 5,
                itemSize: 24,
                ignoreGestures: alreadyRated,
                itemBuilder: (context, _) =>
                    const Icon(Icons.star, color: Colors.amber),
                onRatingUpdate: (rating) {
                  setState(() {
                    tempRatings[detailId] = rating;
                  });
                },
              ),
              const Spacer(),
              if (!alreadyRated && tempRatings.containsKey(detailId))
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  onPressed: () =>
                      _submitRating(detailId, tempRatings[detailId]!),
                  child: const Text('Kirim'),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPaginationControls() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      color: Colors.white,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          IconButton(
            icon: const Icon(Icons.chevron_left),
            onPressed:
                currentPage > 1 ? () => _changePage(currentPage - 1) : null,
          ),
          Text('Halaman $currentPage dari $totalPages'),
          IconButton(
            icon: const Icon(Icons.chevron_right),
            onPressed: hasNextPage ? () => _changePage(currentPage + 1) : null,
          ),
        ],
      ),
    );
  }

  void _changePage(int newPage) {
    setState(() {
      currentPage = newPage;
    });
    fetchOrders();
  }
}
