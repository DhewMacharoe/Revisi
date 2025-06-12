import 'package:Delbites/main_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart'; // 1. Import
import 'package:intl/date_symbol_data_local.dart'; // 2. Import

void main() async { // Ubah menjadi async
  // 3. Tambahkan 2 baris ini
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('id_ID', null);

  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  // timezone initialization sudah tidak diperlukan di sini jika sudah diatur di tempat lain
  // tz.initializeTimeZones();
  // tz.setLocalLocation(tz.getLocation('Asia/Jakarta'));

  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  @override
  void initState() {
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'DelBites',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        fontFamily: 'Inter',
      ),
      // 4. Tambahkan properti lokalisasi ini
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: const [
        Locale('id', 'ID'), // Tambahkan support untuk Bahasa Indonesia
      ],
      locale: const Locale('id', 'ID'), // Set default locale ke Indonesia
      home: const Stack(
        children: [
          MainScreen(),
        ],
      ),
    );
  }
}
