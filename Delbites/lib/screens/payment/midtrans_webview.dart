import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:Delbites/services/midtrans_service.dart';

class MidtransPaymentPage extends StatefulWidget {
  final String redirectUrl;
  final String orderId;

  const MidtransPaymentPage({
    Key? key,
    required this.redirectUrl,
    required this.orderId,
  }) : super(key: key);

  @override
  State<MidtransPaymentPage> createState() => _MidtransPaymentPageState();
}

class _MidtransPaymentPageState extends State<MidtransPaymentPage> {
  late WebViewController _controller;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (String url) {
            setState(() {
              isLoading = true;
            });
          },
          onPageFinished: (String url) {
            setState(() {
              isLoading = false;
            });
            
            // Check if the URL contains success or failure indicators
            if (url.contains('transaction_status=settlement') || 
                url.contains('transaction_status=capture') ||
                url.contains('status_code=200')) {
              // Payment successful
              _handlePaymentSuccess();
            } else if (url.contains('transaction_status=deny') || 
                      url.contains('transaction_status=cancel') ||
                      url.contains('transaction_status=expire') ||
                      url.contains('status_code=202')) {
              // Payment failed
              _handlePaymentFailure();
            }
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.redirectUrl));
  }

  void _handlePaymentSuccess() {
    // Check transaction status with backend
    MidtransService.checkTransactionStatus(widget.orderId).then((response) {
      Navigator.pushReplacementNamed(
        context, 
        '/payment-success',
        arguments: {
          'order_id': widget.orderId,
          'transaction_status': response['transaction_status'] ?? 'success',
        },
      );
    }).catchError((error) {
      // Even if status check fails, assume success if we got here
      Navigator.pushReplacementNamed(
        context, 
        '/payment-success',
        arguments: {
          'order_id': widget.orderId,
          'transaction_status': 'success',
        },
      );
    });
  }

  void _handlePaymentFailure() {
    Navigator.pushReplacementNamed(
      context, 
      '/payment-failed',
      arguments: {
        'order_id': widget.orderId,
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Payment', style: TextStyle(color: Colors.white)),
        backgroundColor: const Color(0xFF2D5EA2),
        leading: IconButton(
          icon: const Icon(Icons.close, color: Colors.white),
          onPressed: () {
            showDialog(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Cancel Payment'),
                content: const Text('Are you sure you want to cancel this payment?'),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('No'),
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.pop(context); // Close dialog
                      Navigator.pop(context); // Close payment page
                    },
                    child: const Text('Yes'),
                  ),
                ],
              ),
            );
          },
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (isLoading)
            const Center(
              child: CircularProgressIndicator(),
            ),
        ],
      ),
    );
  }
}