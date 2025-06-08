import 'dart:convert';

import 'package:Delbites/profile.dart';
import 'package:Delbites/services/menu_services.dart';
import 'package:Delbites/widgets/menu_card.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000';

class HomePage extends StatefulWidget {
  const HomePage({Key? key}) : super(key: key);

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  final TextEditingController _searchController = TextEditingController();
  bool isTokoBuka = true;
  String pesanToko = '';

  List<Map<String, String>> allItems = [];
  List<Map<String, String>> displayedItems = [];
  bool isLoading = true;
  String searchQuery = '';
  String selectedCategory = 'makanan';
  int? idPelanggan;
  String? namaPelanggan;
  bool noSalesDataForRecommendation = false;

  @override
  void initState() {
    super.initState();
    checkOperationalStatus();
    loadPelangganInfo(); // Panggil di init
    fetchMenu();
  }

  // loadPelangganInfo kini hanya memuat dari SharedPreferences
  Future<void> loadPelangganInfo() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      // Pastikan widget masih ada sebelum setState
      setState(() {
        idPelanggan = prefs.getInt('id_pelanggan');
        namaPelanggan = prefs.getString('nama_pelanggan');
      });
    }
  }

  // PENTING: Fungsi ini yang akan dipanggil untuk memperbarui nama dari API
  // mirip dengan bagaimana ProfilePage memuat data profil.
  Future<void> _fetchAndSetPelangganNameFromApi() async {
    final prefs = await SharedPreferences.getInstance();
    final int? currentId = prefs.getInt('id_pelanggan');

    if (currentId != null) {
      try {
        final response = await http.get(
          Uri.parse('$baseUrl/api/pelanggan/$currentId'),
          headers: {'Accept': 'application/json'},
        );

        if (response.statusCode == 200) {
          final Map<String, dynamic> data = jsonDecode(response.body);
          if (mounted) {
            setState(() {
              namaPelanggan = data['nama'] ?? 'Pengguna'; // Perbarui nama
            });
            // Opsional: Perbarui SharedPreferences dengan nama terbaru
            await prefs.setString('nama_pelanggan', namaPelanggan!);
          }
        } else {
          // Jika gagal fetch dari API, coba fallback ke SharedPreferences
          if (mounted) {
            setState(() {
              namaPelanggan = prefs.getString('nama_pelanggan') ?? 'Pengguna';
            });
          }
        }
      } catch (e) {
        // Jika ada error, coba fallback ke SharedPreferences
        if (mounted) {
          setState(() {
            namaPelanggan = prefs.getString('nama_pelanggan') ?? 'Pengguna';
          });
        }
        print('Error fetching updated name from API: $e');
      }
    } else {
      if (mounted) {
        setState(() {
          namaPelanggan = null; // Jika ID tidak ada, anggap tidak login
        });
      }
    }
  }

  Future<void> checkOperationalStatus() async {
    try {
      final response =
          await http.get(Uri.parse('$baseUrl/api/operasional/status'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          isTokoBuka = data['status'] == 'buka';
          pesanToko = data['message'];
        });

        if (!isTokoBuka) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(pesanToko),
                duration: const Duration(seconds: 5),
                behavior: SnackBarBehavior.floating,
                margin: const EdgeInsets.all(20),
              ),
            );
          }
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal memeriksa status toko.')),
        );
      }
    }
  }

  Future<void> fetchMenu() async {
    setState(() => isLoading = true);
    try {
      allItems = await MenuService.fetchMenu();
      allItems.sort((a, b) => (b['name'] ?? '').compareTo(a['name'] ?? ''));

      setState(() {
        filterCategory(selectedCategory, initialLoad: true);
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Terjadi kesalahan: $e')),
        );
      }
    }
  }

  void filterCategory(String category, {bool initialLoad = false}) {
    List<Map<String, String>> tempItems;
    bool newNoSalesDataFlag = false;

    if (category == 'Rekomendasi') {
      bool hasAnySales = allItems.any((item) =>
          item['stok_terjual'] != null &&
          int.tryParse(item['stok_terjual']!) != null &&
          int.parse(item['stok_terjual']!) > 0);

      if (!hasAnySales) {
        newNoSalesDataFlag = true;
        tempItems = [];
      } else {
        newNoSalesDataFlag = false;
        tempItems = allItems
            .where((item) =>
                item['stok'] != null &&
                int.tryParse(item['stok']!) != null &&
                int.parse(item['stok']!) > 0 &&
                item['stok_terjual'] != null &&
                int.tryParse(item['stok_terjual']!) != null &&
                int.parse(item['stok_terjual']!) > 0)
            .toList()
          ..sort((a, b) => int.parse(b['stok_terjual']!)
              .compareTo(int.parse(a['stok_terjual']!)));
        tempItems = tempItems.take(8).toList();
      }
    } else {
      newNoSalesDataFlag = false;
      tempItems =
          allItems.where((item) => item['kategori'] == category).toList();
    }

    if (initialLoad) {
      setState(() {
        selectedCategory = category;
        displayedItems = tempItems;
        noSalesDataForRecommendation = newNoSalesDataFlag;
        if (searchQuery.isNotEmpty) {
          applySearch(searchQuery,
              updateState: true, sourceForSearch: tempItems);
        }
      });
    } else {
      setState(() {
        selectedCategory = category;
        noSalesDataForRecommendation = newNoSalesDataFlag;
        if (searchQuery.isNotEmpty) {
          applySearch(searchQuery,
              updateState: true, sourceForSearch: tempItems);
        } else {
          displayedItems = tempItems;
        }
      });
    }
  }

  void applySearch(String query,
      {bool updateState = true, List<Map<String, String>>? sourceForSearch}) {
    List<Map<String, String>> sourceItems;

    if (sourceForSearch != null) {
      sourceItems = sourceForSearch;
    } else {
      if (selectedCategory == 'Rekomendasi') {
        if (noSalesDataForRecommendation) {
          sourceItems = [];
        } else {
          sourceItems = allItems
              .where((item) =>
                  item['stok'] != null &&
                  int.tryParse(item['stok']!) != null &&
                  int.parse(item['stok']!) > 0 &&
                  item['stok_terjual'] != null &&
                  int.tryParse(item['stok_terjual']!) != null &&
                  int.parse(item['stok_terjual']!) > 0)
              .toList()
            ..sort((a, b) => int.parse(b['stok_terjual']!)
                .compareTo(int.parse(a['stok_terjual']!)));
          sourceItems = sourceItems.take(8).toList();
        }
      } else {
        sourceItems = allItems
            .where((item) => item['kategori'] == selectedCategory)
            .toList();
      }
    }

    final filtered = sourceItems
        .where(
            (item) => item['name']!.toLowerCase().contains(query.toLowerCase()))
        .toList();

    if (updateState) {
      setState(() {
        searchQuery = query;
        displayedItems = filtered;
      });
    } else {
      displayedItems = filtered;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.white, Color(0xFFE6EBF5)],
          ),
        ),
        child: Column(
          children: [
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    const Color(0xFF2D5EA2),
                    const Color(0xFF2D5EA2).withOpacity(0.95),
                    const Color(0xFF2D5EA2).withOpacity(0.85),
                    const Color(0xFF2D5EA2).withOpacity(0.7),
                    const Color(0xFF2D5EA2).withOpacity(0.4),
                    const Color(0xFF2D5EA2).withOpacity(0.0),
                  ],
                  stops: const [0.0, 0.25, 0.5, 0.75, 0.9, 1.0],
                ),
              ),
              child: Column(
                children: [
                  _buildHeaderContent(),
                  _buildSearchBar(),
                ],
              ),
            ),
            _buildCategorySelector(),
            const SizedBox(height: 8),
            Expanded(
              child: isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : RefreshIndicator(
                      onRefresh: fetchMenu,
                      child: _buildMenuGrid(),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeaderContent() {
    return SafeArea(
      bottom: false,
      child: Padding(
        padding:
            const EdgeInsets.only(top: 20, left: 20, right: 20, bottom: 10),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Flexible(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Selamat Datang ${namaPelanggan ?? 'Pengguna'}\ndi DelBites', // Default "Pengguna" jika nama null
                    style: const TextStyle(
                      fontSize: 18,
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                    overflow: TextOverflow.ellipsis,
                    maxLines: 2,
                  ),
                ],
              ),
            ),
            IconButton(
              icon: const Icon(Icons.person, size: 30, color: Colors.white),
              onPressed: () async {
                final prefs = await SharedPreferences.getInstance();
                final id = prefs.getInt('id_pelanggan');
                if (mounted) {
                  // Gunakan then() untuk menunggu hasil dari navigasi
                  // dan muat ulang nama pelanggan setelah kembali
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => ProfilePage(idPelanggan: id ?? 0),
                    ),
                  ).then((_) {
                    // Panggil fungsi untuk memperbarui nama pelanggan setelah kembali dari ProfilePage
                    // Ini akan mengambil nama terbaru dari SharedPreferences (atau API jika di ProfilePage ada update ke Prefs)
                    _fetchAndSetPelangganNameFromApi(); // Memuat ulang dari API untuk memastikan yang terbaru
                  });
                }
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
      child: TextField(
        controller: _searchController,
        onChanged: (query) {
          applySearch(query);
        },
        decoration: InputDecoration(
          hintText: 'Cari menu...',
          prefixIcon: const Icon(Icons.search),
          suffixIcon: searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () {
                    _searchController.clear();
                    setState(() {
                      searchQuery = '';
                      filterCategory(selectedCategory);
                    });
                  })
              : null,
          filled: true,
          fillColor: Colors.white.withOpacity(0.9),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(20),
            borderSide: BorderSide.none,
          ),
          contentPadding: const EdgeInsets.symmetric(vertical: 10.0),
        ),
      ),
    );
  }

  Widget _buildCategorySelector() {
    final categories = ["Rekomendasi", "makanan", "minuman"];
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: categories
              .map((cat) => CategoryButton(
                    label: cat,
                    isSelected: selectedCategory == cat,
                    onTap: () => filterCategory(cat),
                  ))
              .toList(),
        ),
      ),
    );
  }

  Widget _buildMenuGrid() {
    if (displayedItems.isEmpty && searchQuery.isNotEmpty && !isLoading) {
      return const Center(
        child: Text(
          'Menu tidak ditemukan',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
      );
    }

    if (displayedItems.isEmpty && !isLoading) {
      String message;
      if (selectedCategory == 'Rekomendasi') {
        if (noSalesDataForRecommendation) {
          message = 'Belum ada rekomendasi';
        } else {
          message = 'Tidak ada rekomendasi untuk ditampilkan saat ini';
        }
      } else {
        message = 'Tidak ada menu di kategori ini';
      }
      return Center(
        child: Text(
          message,
          style: TextStyle(fontSize: 16, color: Colors.grey[700]),
          textAlign: TextAlign.center,
        ),
      );
    }

    return GridView.builder(
      padding: const EdgeInsets.all(15),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 15,
        mainAxisSpacing: 15,
        childAspectRatio: 0.75,
      ),
      itemCount: displayedItems.length,
      itemBuilder: (context, index) {
        final item = displayedItems[index];
        return MenuCard(item: item);
      },
    );
  }
}

class CategoryButton extends StatelessWidget {
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const CategoryButton({
    Key? key,
    required this.label,
    required this.isSelected,
    required this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: ElevatedButton(
        onPressed: onTap,
        style: ElevatedButton.styleFrom(
          backgroundColor: isSelected
              ? const Color(0xFF2D5EA2)
              : const Color.fromARGB(255, 224, 224, 224),
          foregroundColor: isSelected ? Colors.white : Colors.black87,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        ),
        child: Text(
          label,
        ),
      ),
    );
  }
}
