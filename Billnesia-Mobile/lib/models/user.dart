class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final int? parentId;
  final int? planId;
  final String? createdAt;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.parentId,
    this.planId,
    this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? '',
      parentId: json['parent_id'],
      planId: json['plan_id'],
      createdAt: json['created_at'],
    );
  }
}
