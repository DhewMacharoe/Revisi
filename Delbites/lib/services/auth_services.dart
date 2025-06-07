import 'dart:convert';

import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  final FirebaseAuth _auth = FirebaseAuth.instance;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  String _verificationId = '';
  static const String baseUrl = 'http://127.0.0.1:8000';

  Future<bool> sendOTP(String phoneNumber) async {
    try {
      await _auth.verifyPhoneNumber(
        phoneNumber: phoneNumber,
        timeout: const Duration(seconds: 60),
        verificationCompleted: (PhoneAuthCredential credential) async {
          await _auth.signInWithCredential(credential);
        },
        verificationFailed: (FirebaseAuthException e) {
          print('Error: ${e.message}');
        },
        codeSent: (String verificationId, int? resendToken) {
          _verificationId = verificationId;
        },
        codeAutoRetrievalTimeout: (String verificationId) {
          _verificationId = verificationId;
        },
      );
      return true;
    } catch (e) {
      print('Error sending OTP: $e');
      return false;
    }
  }

  // Verifikasi OTP
  Future<bool> verifyOTP(String otp) async {
    try {
      PhoneAuthCredential credential = PhoneAuthProvider.credential(
        verificationId: _verificationId,
        smsCode: otp,
      );
      await _auth.signInWithCredential(credential);
      return true;
    } catch (e) {
      print('Error verifying OTP: $e');
      return false;
    }
  }

  // Registrasi User
  Future<bool> registerUser(
      String username, String phone, String password) async {
    try {
      await _firestore.collection('users').doc(phone).set({
        'username': username,
        'phone': phone,
        'password': password,
      });
      return true;
    } catch (e) {
      print('Error registering user: $e');
      return false;
    }
  }

  // Login User
  Future<bool> loginUser(String phone, String password) async {
    try {
      DocumentSnapshot userDoc =
          await _firestore.collection('users').doc(phone).get();
      if (userDoc.exists) {
        String savedPassword = userDoc['password'];
        if (savedPassword == password) {
          return true;
        }
      }
      return false;
    } catch (e) {
      print('Error logging in: $e');
      return false;
    }
  }

  static Future<bool> verifyOTPAndLogin({
    required String otp,
    required String phone,
    required String verificationId,
    String? nama,
  }) async {
    try {
      final credential = PhoneAuthProvider.credential(
        verificationId: verificationId,
        smsCode: otp,
      );

      await FirebaseAuth.instance.signInWithCredential(credential);

      final response = await http.post(
        Uri.parse('$baseUrl/api/auth/pelanggan'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'no_hp': phone,
          'nama': nama ?? 'Pelanggan',
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setBool('isLoggedIn', true);
        await prefs.setInt('id_pelanggan', data['id_pelanggan']);
        await prefs.setString('nama', data['nama']);
        await prefs.setString('no_hp', data['no_hp']);
        return true;
      } else {
        return false;
      }
    } catch (e) {
      print('Error in verifyOTPAndLogin: $e');
      return false;
    }
  }

  static Future<Map<String, dynamic>> loginManual(
      String noHp, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/auth/pelanggan/manual'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'no_hp': noHp,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {
          'success': true,
          ...data,
        };
      } else {
        return {
          'success': false,
          'message': jsonDecode(response.body)['error'] ?? 'Gagal login',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }
}
