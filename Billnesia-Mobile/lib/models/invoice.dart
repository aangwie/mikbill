class Invoice {
  final int id;
  final int customerId;
  final String? dueDate;
  final double price;
  final String status;
  final String? customerName;
  final String? internetNumber;

  Invoice({
    required this.id,
    required this.customerId,
    this.dueDate,
    required this.price,
    required this.status,
    this.customerName,
    this.internetNumber,
  });

  factory Invoice.fromJson(Map<String, dynamic> json) {
    final customer = json['customer'];
    return Invoice(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      customerId: int.tryParse(json['customer_id']?.toString() ?? '0') ?? 0,
      dueDate: json['due_date'],
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0,
      status: json['status'] ?? 'unpaid',
      customerName: customer?['name'],
      internetNumber: customer?['internet_number'],
    );
  }

  bool get isPaid => status == 'paid';
}
