import 'dart:convert';

import 'package:http/http.dart' as http;

Future<List<Map<String, String>>> fetchOrders() async {
  final String apiUrl = 'http://localhost/api/pemesanan';

  try {
    final response = await http.get(Uri.parse(apiUrl));

    if (response.statusCode == 200) {
      List<dynamic> data = json.decode(response.body);
      return data.map((order) {
        return {
          'name': order['pelanggan']['name']?.toString() ?? '',
          'quantity': order['quantity']?.toString() ?? '',
          'payment': order['metode_pembayaran']?.toString() ?? '',
          'date': order['waktu_pemesanan']?.toString() ?? '',
          'price': order['total_harga']?.toString() ?? '',
          'status': order['status']?.toString() ?? '',
        };
      }).toList();
    } else {
      throw Exception('Failed to load orders');
    }
  } catch (e) {
    throw Exception('Failed to load orders: $e');
  }
}
