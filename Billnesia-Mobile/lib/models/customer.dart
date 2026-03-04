class Customer {
  final int id;
  final String name;
  final String? internetNumber;
  final String? pppoeUsername;
  final String? pppoePassword;
  final String? address;
  final String? phone;
  final double? monthlyPrice;
  final bool isActive;
  final int? operatorId;
  final int? adminId;

  Customer({
    required this.id,
    required this.name,
    this.internetNumber,
    this.pppoeUsername,
    this.pppoePassword,
    this.address,
    this.phone,
    this.monthlyPrice,
    this.isActive = true,
    this.operatorId,
    this.adminId,
  });

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name'] ?? '',
      internetNumber: json['internet_number'],
      pppoeUsername: json['pppoe_username'],
      pppoePassword: json['pppoe_password'],
      address: json['address'],
      phone: json['phone'],
      monthlyPrice: json['monthly_price'] != null
          ? double.tryParse(json['monthly_price'].toString())
          : null,
      isActive: json['is_active'] == true || json['is_active'] == 1,
      operatorId: json['operator_id'] != null
          ? int.tryParse(json['operator_id'].toString())
          : null,
      adminId: json['admin_id'] != null
          ? int.tryParse(json['admin_id'].toString())
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'internet_number': internetNumber,
      'pppoe_username': pppoeUsername,
      'pppoe_password': pppoePassword,
      'address': address,
      'phone': phone,
      'monthly_price': monthlyPrice,
      'is_active': isActive,
    };
  }
}
