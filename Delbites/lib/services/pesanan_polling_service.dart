import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

class PesananPollingService {
  final String baseUrl;
  DateTime lastChecked = DateTime.now();

  PesananPollingService({required this.baseUrl});

  Timer? _timer;

  void startPolling(void Function(int newOrders) onNewOrders) {
    _timer = Timer.periodic(Duration(seconds: 10), (_) async {
      try {
        final response = await http.get(
          Uri.parse(
              '$baseUrl/pemesanan/cek-baru?last_checked=${lastChecked.toIso8601String()}'),
        );
        if (response.statusCode == 200) {
          final data = json.decode(response.body);
          int newOrders = data['new_orders'] ?? 0;
          String timestamp = data['timestamp'];
          lastChecked = DateTime.parse(timestamp);
          if (newOrders > 0) {
            onNewOrders(newOrders);
          }
        }
      } catch (e) {
        print('Polling error: $e');
      }
    });
  }

  void stopPolling() {
    _timer?.cancel();
  }
}
