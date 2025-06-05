import 'package:Delbites/profile.dart';
import 'package:Delbites/services/menu_services.dart';
import 'package:Delbites/widgets/menu_card.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String baseUrl = 'https://delbites.d4trpl-itdel.id';

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
  String selectedCategory = 'Rekomendasi';
  int? idPelanggan;
  String? namaPelanggan; // Menambahkan variabel untuk nama pelanggan

  @override
  void initState() {
    super.initState();
    loadPelangganInfo();
    fetchMenu();
  }

  // Fungsi untuk memuat informasi pelanggan (ID dan Nama) dari SharedPreferences
  Future<void> loadPelangganInfo() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      idPelanggan = prefs.getInt('id_pelanggan');
      namaPelanggan =
          prefs.getString('nama_pelanggan'); // Memuat nama pelanggan
    });
  }

  Future<void> fetchMenu() async {
    setState(() => isLoading = true);
    try {
      allItems = await MenuService.fetchMenu();
      allItems.sort((a, b) => int.parse(b['stok_terjual']!)
          .compareTo(int.parse(a['stok_terjual']!)));
      setState(() {
        displayedItems = allItems.take(8).toList();
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

  void filterCategory(String category) {
    setState(() {
      selectedCategory = category;

      if (category == 'Rekomendasi') {
        displayedItems = allItems.where((item) => item['stok'] != null).toList()
          ..sort((a, b) => int.parse(b['stok_terjual']!)
              .compareTo(int.parse(a['stok_terjual']!)));
        displayedItems = displayedItems.take(8).toList();
      } else if (category == 'Semua') {
        displayedItems = allItems;
      } else {
        displayedItems =
            allItems.where((item) => item['kategori'] == category).toList();
      }

      if (searchQuery.isNotEmpty) {
        applySearch(searchQuery, updateState: false);
      }
    });
  }

  void applySearch(String query, {bool updateState = true}) {
    List<Map<String, String>> sourceItems;

    if (selectedCategory == 'Rekomendasi') {
      sourceItems = allItems.where((item) => item['stok'] != null).toList()
        ..sort((a, b) => int.parse(b['stok_terjual']!)
            .compareTo(int.parse(a['stok_terjual']!)));
      sourceItems = sourceItems.take(8).toList();
    } else if (selectedCategory == 'Semua') {
      sourceItems = allItems;
    } else {
      sourceItems = allItems
          .where((item) => item['kategori'] == selectedCategory)
          .toList();
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
            _buildHeader(),
            _buildSearchBar(),
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

  // Widget Header
  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            const Color(0xFF2D5EA2),
            const Color(0xFF2D5EA2).withOpacity(0.9),
            const Color(0xFF2D5EA2).withOpacity(0.8),
            const Color(0xFF2D5EA2).withOpacity(0.6),
            const Color(0xFF2D5EA2).withOpacity(0.3),
            const Color.fromARGB(0, 255, 255, 255),
          ],
        ),
      ),
      child: SafeArea(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Flexible(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    // Menggunakan nama pelanggan jika tersedia, jika tidak, gunakan "DelBites"
                    'Selamat Datang ${namaPelanggan ?? ''} di DelBites',
                    style: const TextStyle(
                      fontSize: 18,
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                    overflow: TextOverflow.ellipsis,
                    maxLines: 2,
                  ),
                  // Menghapus Image.asset di sini
                ],
              ),
            ),
            // Mengubah ikon keranjang menjadi ikon profil
            IconButton(
              icon: const Icon(Icons.person, size: 30, color: Colors.white),
              onPressed: () async {
                final prefs = await SharedPreferences.getInstance();
                final id = prefs.getInt('id_pelanggan');

                // Navigasi ke ProfilePage
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => ProfilePage(
                        idPelanggan:
                            id ?? 0), // Mengirim 0 jika idPelanggan null
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  // Widget Search Bar
  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: TextField(
        controller: _searchController,
        onChanged: applySearch,
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
                      displayedItems = allItems
                          .where((item) => int.parse(item['stok']!) > 0)
                          .toList();
                    });
                  })
              : null,
          filled: true,
          fillColor: Colors.grey[200],
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(20),
            borderSide: BorderSide.none,
          ),
        ),
      ),
    );
  }

  // Widget Pemilih Kategori
  Widget _buildCategorySelector() {
    final categories = ["Rekomendasi", "makanan", "minuman"];
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 10),
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

  // Widget Grid Menu
  Widget _buildMenuGrid() {
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
              : const Color.fromARGB(255, 161, 161, 161),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
        ),
        child: Text(
          label,
          style: const TextStyle(color: Colors.white),
        ),
      ),
    );
  }
}
