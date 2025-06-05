// import 'package:Delbites/services/pelanggan_services.dart';
// import 'package:flutter/material.dart';
// import 'package:shared_preferences/shared_preferences.dart';

// class IsiDataPelanggan extends StatefulWidget {
//   @override
//   _IsiDataPelangganState createState() => _IsiDataPelangganState();
// }

// class _IsiDataPelangganState extends State<IsiDataPelanggan> {
//   final _namaController = TextEditingController();
//   final _teleponController = TextEditingController();

//   Future<void> _saveData() async {
//     final nama = _namaController.text.trim();
//     final telepon = _teleponController.text.trim();
//     print("Attempting to save: Nama=$nama, Telepon=$telepon"); // Debug log

//     try {
//       final result = await PelangganService().createPelanggan(
//         nama: nama,
//         telepon: telepon,
//       );
//       print("Server response: $result"); // Debug log

//       final prefs = await SharedPreferences.getInstance();
//       await prefs.setInt('id_pelanggan', result['id']);
//       await prefs.setString('nama_pelanggan', result['nama']);
//       await prefs.setString('telepon_pelanggan', result['telepon']);

//       ScaffoldMessenger.of(context).showSnackBar(
//         SnackBar(
//             content: Text('Pelanggan berhasil disimpan: ${result['nama']}')),
//       );

//       // Arahkan ke halaman utama
//       Navigator.pushReplacementNamed(context, '/');
//     } catch (e) {
//       print("Error occurred: $e"); // Debug log
//       ScaffoldMessenger.of(context).showSnackBar(
//         SnackBar(content: Text('Gagal menyimpan: ${e.toString()}')),
//       );
//     }
//   }

//   @override
//   Widget build(BuildContext context) {
//     return Scaffold(
//       appBar: AppBar(title: const Text("Isi Data Pelanggan")),
//       body: Padding(
//         padding: const EdgeInsets.all(16.0),
//         child: Column(
//           children: [
//             TextField(
//               controller: _namaController,
//               decoration: const InputDecoration(labelText: 'Nama Lengkap'),
//             ),
//             TextField(
//               controller: _teleponController,
//               decoration: const InputDecoration(labelText: 'Nomor WhatsApp'),
//               keyboardType: TextInputType.phone,
//             ),
//             const SizedBox(height: 20),
//             ElevatedButton(
//               onPressed: _saveData,
//               child: const Text('Simpan Data'),
//             ),
//           ],
//         ),
//       ),
//     );
//   }
// }
