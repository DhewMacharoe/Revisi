import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:Delbites/screens/payment/midtrans_webview.dart';

const String baseUrl = 'https://delbites.d4trpl-itdel.id';

class MidtransService {
  // Create a payment transaction
  static Future<void> createMidtransTransaction({
  required int grossAmount,
  required int idPemesanan,
  required List<Map<String, dynamic>> items,
  required String firstName,
  required String lastName,
  required String email,
}) async {
  final response = await http.post(
    Uri.parse('$baseUrl/api/midtrans/create-transaction'),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: jsonEncode({
      "gross_amount": grossAmount,
      "id_pemesanan": idPemesanan,
      "customer": {
        "first_name": firstName,
        "last_name": lastName,
        "email": email,
      },
      "items": items
    }),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    print("Redirect URL: ${data['redirect_url']}");
    // Navigasi ke WebView atau MidtransPaymentPage
  } else {
    throw Exception("Gagal membuat transaksi: ${response.body}");
  }
}


  // Check transaction status
  static Future<Map<String, dynamic>> checkTransactionStatus(
      String orderId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/midtrans/status/$orderId'),
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to check transaction status: ${response.body}');
      }
    } catch (e) {
      throw Exception('Error checking transaction status: $e');
    }
  }

  // Open payment page in WebView
  static void openPaymentPage(
      BuildContext context, String redirectUrl, String orderId) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => MidtransPaymentPage(
          redirectUrl: redirectUrl,
          orderId: orderId,
        ),
      ),
    );
  }
}
