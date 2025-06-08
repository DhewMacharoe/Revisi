import 'dart:convert'; // Tambahkan ini

import 'package:Delbites/login_page.dart'; // Pastikan Anda memiliki LoginPage.dart
import 'package:Delbites/main_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http; // Tambahkan ini
import 'package:shared_preferences/shared_preferences.dart'; // Tambahkan ini
import 'package:timezone/data/latest.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

const String baseUrl =
    'http://127.0.0.1:8000'; // Pastikan ini sesuai dengan URL API Anda

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  tz.initializeTimeZones();
  tz.setLocalLocation(tz.getLocation('Asia/Jakarta'));
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  // Fungsi untuk menentukan halaman awal berdasarkan status login dan validasi API
  Future<Widget> _getInitialScreen() async {
    final prefs = await SharedPreferences.getInstance();
    final int? storedId = prefs.getInt('id_pelanggan');
    final bool? isLoggedIn = prefs.getBool('isLoggedIn');
    if (storedId == null || isLoggedIn != true) {
      await prefs.clear(); // Pastikan SharedPreferences bersih
      return const LoginPage();
    }

    // Jika ada ID dan status login true, coba validasi dengan API backend
    try {
      final response = await http.get(
        Uri.parse(
            '$baseUrl/api/pelanggan/$storedId'), // Endpoint API untuk mengambil data pelanggan berdasarkan ID
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        // Data pelanggan ditemukan dan valid di database
        final Map<String, dynamic> data = jsonDecode(response.body);

        // Perbarui SharedPreferences dengan data terbaru dari API (opsional, tapi disarankan)
        await prefs.setString('nama_pelanggan', data['nama'] ?? '');
        await prefs.setString('telepon_pelanggan', data['telepon'] ?? '');
        await prefs.setString('email_pelanggan', data['email'] ?? '');

        return const MainScreen(); // Pengguna valid, arahkan ke MainScreen
      } else {
        // API mengembalikan status selain 200 (misalnya 404 Not Found, 500 Server Error)
        // Ini berarti ID di SharedPreferences tidak valid atau tidak ada di database
        await prefs.clear(); // Bersihkan SharedPreferences
        return const LoginPage(); // Paksa ke LoginPage
      }
    } catch (e) {
      // Terjadi error jaringan atau masalah parsing JSON
      print('Error during initial API validation: $e');
      await prefs.clear();
      return const LoginPage();
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'DelBites',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: FutureBuilder<Widget>(
        future: _getInitialScreen(),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Scaffold(
              body: Center(
                child: CircularProgressIndicator(),
              ),
            );
          } else if (snapshot.hasError) {
            return Scaffold(
              body: Center(
                child: Text('Error loading app: ${snapshot.error}'),
              ),
            );
          } else {
            return snapshot.data ?? const LoginPage();
          }
        },
      ),
    );
  }
}
