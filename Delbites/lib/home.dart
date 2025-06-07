import 'package:Delbites/profile.dart';
import 'package:Delbites/services/menu_services.dart';
import 'package:Delbites/widgets/menu_card.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'http://127.0.0.1:8000';

class HomePage extends StatefulWidget {
  const HomePage({Key? key}) : super(key: key);

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  final TextEditingController _searchController = TextEditingController();

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
    loadPelangganInfo();
    fetchMenu();
  }

  Future<void> loadPelangganInfo() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      idPelanggan = prefs.getInt('id_pelanggan');
      namaPelanggan = prefs.getString('nama_pelanggan');
    });
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
        // Gradasi latar belakang utama untuk seluruh halaman
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.white, Color(0xFFE6EBF5)],
          ),
        ),
        child: Column(
          children: [
            // Container baru untuk header dan search bar dengan gradasi gabungan
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    const Color(0xFF2D5EA2),
                    const Color(0xFF2D5EA2)
                        .withOpacity(0.95), // Sedikit lebih pekat
                    const Color(0xFF2D5EA2).withOpacity(0.85),
                    const Color(0xFF2D5EA2).withOpacity(0.7),
                    const Color(0xFF2D5EA2)
                        .withOpacity(0.4), // Lebih transparan di bawah
                    const Color(0xFF2D5EA2)
                        .withOpacity(0.0), // Fading ke transparan
                  ],
                  stops: const [
                    0.0,
                    0.25,
                    0.5,
                    0.75,
                    0.9,
                    1.0
                  ], // Sesuaikan stop untuk blending
                ),
              ),
              child: Column(
                children: [
                  _buildHeaderContent(), // Konten header (teks & ikon profil)
                  _buildSearchBar(), // Search bar
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

  // Widget untuk konten header (tanpa Container gradasi luar)
  Widget _buildHeaderContent() {
    return SafeArea(
      // SafeArea untuk bagian atas saja, karena search bar di bawahnya
      bottom: false, // Tidak perlu SafeArea di bawah teks header ini
      child: Padding(
        padding: const EdgeInsets.only(
            top: 20,
            left: 20,
            right: 20,
            bottom: 10), // Sesuaikan padding jika perlu
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment:
              CrossAxisAlignment.center, // Pusatkan ikon profil secara vertikal
          children: [
            Flexible(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize
                    .min, // Agar Column tidak mengambil tinggi berlebih
                children: [
                  Text(
                    'Selamat Datang ${namaPelanggan ?? ''}\ndi DelBites',
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
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => ProfilePage(idPelanggan: id ?? 0),
                    ),
                  );
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
      // Padding search bar akan berada di dalam area gradasi
      padding: const EdgeInsets.fromLTRB(16, 0, 16,
          16), // Kurangi padding atas jika header sudah memberi jarak
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
          fillColor: Colors.white.withOpacity(
              0.9), // Buat sedikit transparan agar gradasi samar terlihat, atau tetap grey[200]
          // fillColor: Colors.grey[200], // Alternatif jika ingin solid
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(20),
            borderSide: BorderSide.none,
          ),
          contentPadding: const EdgeInsets.symmetric(
              vertical: 10.0), // Sesuaikan tinggi search bar
        ),
      ),
    );
  }

  Widget _buildCategorySelector() {
    final categories = ["Rekomendasi", "makanan", "minuman"];
    return Padding(
      padding: const EdgeInsets.symmetric(
          horizontal: 10, vertical: 8), // Tambahkan sedikit vertical padding
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
