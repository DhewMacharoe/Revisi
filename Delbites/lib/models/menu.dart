class Menu {
  final int id;
  final String namaMenu;
  final String kategori;
  final int harga;
  final int stok;
  final String? gambar;
  final String? deskripsi;
  final double? rating;

  Menu({
    required this.id,
    required this.namaMenu,
    required this.kategori,
    required this.harga,
    required this.stok,
    this.gambar,
    this.deskripsi,
    this.rating,
  });

  factory Menu.fromJson(Map<String, dynamic> json) {
    return Menu(
      id: json['id'],
      namaMenu: json['nama_menu'],
      kategori: json['kategori'],
      harga: json['harga'],
      stok: json['stok'],
      gambar: json['gambar'],
      deskripsi: json['deskripsi'],
      rating: json['rating']?.toDouble(),
    );
  }
}
