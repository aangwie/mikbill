class DashboardStats {
  final int totalCustomers;
  final int activeCustomers;
  final int disabledCustomers;
  final double totalBilling;
  final double paidBilling;
  final double unpaidBilling;
  final int billingMonth;
  final int billingYear;
  final List<String> chartLabels;
  final List<double> chartPaid;
  final List<double> chartUnpaid;

  DashboardStats({
    required this.totalCustomers,
    required this.activeCustomers,
    required this.disabledCustomers,
    required this.totalBilling,
    required this.paidBilling,
    required this.unpaidBilling,
    required this.billingMonth,
    required this.billingYear,
    required this.chartLabels,
    required this.chartPaid,
    required this.chartUnpaid,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) {
    final customers = json['customers'] ?? {};
    final billing = json['billing'] ?? {};
    final chart = json['chart'] ?? {};

    return DashboardStats(
      totalCustomers: customers['total'] ?? 0,
      activeCustomers: customers['active'] ?? 0,
      disabledCustomers: customers['disabled'] ?? 0,
      totalBilling: (billing['total'] ?? 0).toDouble(),
      paidBilling: (billing['paid'] ?? 0).toDouble(),
      unpaidBilling: (billing['unpaid'] ?? 0).toDouble(),
      billingMonth: billing['month'] ?? DateTime.now().month,
      billingYear: billing['year'] ?? DateTime.now().year,
      chartLabels: List<String>.from(chart['labels'] ?? []),
      chartPaid: ((chart['paid'] as List?) ?? [])
          .map<double>((e) => (e ?? 0).toDouble())
          .toList(),
      chartUnpaid: ((chart['unpaid'] as List?) ?? [])
          .map<double>((e) => (e ?? 0).toDouble())
          .toList(),
    );
  }
}
