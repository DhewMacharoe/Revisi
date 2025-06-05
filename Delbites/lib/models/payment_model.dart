class PaymentTransaction {
  final String orderId;
  final int grossAmount;
  final String firstName;
  final String lastName;
  final String email;
  final List<PaymentItem> items;
  String? redirectUrl;
  String? transactionStatus;
  String? transactionId;

  PaymentTransaction({
    required this.orderId,
    required this.grossAmount,
    required this.firstName,
    required this.lastName,
    required this.email,
    required this.items,
    this.redirectUrl,
    this.transactionStatus,
    this.transactionId,
  });

  Map<String, dynamic> toJson() {
    return {
      'order_id': orderId,
      'gross_amount': grossAmount,
      'first_name': firstName,
      'last_name': lastName,
      'email': email,
      'items': items.map((item) => item.toJson()).toList(),
    };
  }

  factory PaymentTransaction.fromJson(Map<String, dynamic> json) {
    return PaymentTransaction(
      orderId: json['order_id'],
      grossAmount: json['gross_amount'],
      firstName: json['first_name'],
      lastName: json['last_name'],
      email: json['email'],
      items: (json['items'] as List)
          .map((item) => PaymentItem.fromJson(item))
          .toList(),
      redirectUrl: json['redirect_url'],
      transactionStatus: json['transaction_status'],
      transactionId: json['transaction_id'],
    );
  }
}

class PaymentItem {
  final String id;
  final String name;
  final int price;
  final int quantity;

  PaymentItem({
    required this.id,
    required this.name,
    required this.price,
    required this.quantity,
  });

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'price': price,
      'quantity': quantity,
    };
  }

  factory PaymentItem.fromJson(Map<String, dynamic> json) {
    return PaymentItem(
      id: json['id'],
      name: json['name'],
      price: json['price'],
      quantity: json['quantity'],
    );
  }
}