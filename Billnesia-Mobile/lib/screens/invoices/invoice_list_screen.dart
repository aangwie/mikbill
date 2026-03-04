import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/invoice.dart';
import '../../services/api_service.dart';
import '../../widgets/invoice_tile.dart';

class InvoiceListScreen extends StatefulWidget {
  const InvoiceListScreen({super.key});

  @override
  State<InvoiceListScreen> createState() => _InvoiceListScreenState();
}

class _InvoiceListScreenState extends State<InvoiceListScreen> {
  List<Invoice> _invoices = [];
  bool _isLoading = true;
  String? _error;
  int _month = DateTime.now().month;
  int _year = DateTime.now().year;
  String? _statusFilter;
  double _totalBilling = 0;
  double _paidBilling = 0;
  double _unpaidBilling = 0;

  final currencyFormat = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  final List<String> _monthNames = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ];

  @override
  void initState() {
    super.initState();
    _loadInvoices();
  }

  Future<void> _loadInvoices() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final params = <String, String>{
        'month': _month.toString(),
        'year': _year.toString(),
        'per_page': '100',
      };
      if (_statusFilter != null) params['status'] = _statusFilter!;

      final result = await ApiService.get('/invoices', params: params);
      if (result['success'] == true) {
        final data = result['data']['data'] as List;
        final summary = result['summary'] ?? {};
        setState(() {
          _invoices = data.map((e) => Invoice.fromJson(e)).toList();
          _totalBilling = (summary['total'] ?? 0).toDouble();
          _paidBilling = (summary['paid'] ?? 0).toDouble();
          _unpaidBilling = (summary['unpaid'] ?? 0).toDouble();
          _isLoading = false;
        });
      }
    } on ApiException catch (e) {
      setState(() {
        _error = e.message;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Gagal memuat tagihan.';
        _isLoading = false;
      });
    }
  }

  Future<void> _payInvoice(Invoice invoice) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF1E293B),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Konfirmasi Pembayaran',
          style: TextStyle(color: Colors.white),
        ),
        content: Text(
          'Tandai tagihan ${invoice.customerName} sebagai LUNAS?',
          style: const TextStyle(color: Colors.white70),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF10B981),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Bayar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await ApiService.post('/invoices/${invoice.id}/pay');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Pembayaran berhasil!'),
            backgroundColor: const Color(0xFF10B981),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        );
      }
      _loadInvoices();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Period Selector & Summary
        Container(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              // Month/Year Selector
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E293B),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<int>(
                          value: _month,
                          dropdownColor: const Color(0xFF1E293B),
                          style: const TextStyle(color: Colors.white),
                          isExpanded: true,
                          items: List.generate(
                            12,
                            (i) => DropdownMenuItem(
                              value: i + 1,
                              child: Text(_monthNames[i]),
                            ),
                          ),
                          onChanged: (v) {
                            setState(() => _month = v!);
                            _loadInvoices();
                          },
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E293B),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<int>(
                        value: _year,
                        dropdownColor: const Color(0xFF1E293B),
                        style: const TextStyle(color: Colors.white),
                        items: List.generate(3, (i) {
                          final y = DateTime.now().year - 1 + i;
                          return DropdownMenuItem(value: y, child: Text('$y'));
                        }),
                        onChanged: (v) {
                          setState(() => _year = v!);
                          _loadInvoices();
                        },
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Summary Row
              if (!_isLoading)
                Row(
                  children: [
                    _summaryChip(
                      'Total',
                      currencyFormat.format(_totalBilling),
                      Colors.white,
                    ),
                    const SizedBox(width: 8),
                    _summaryChip(
                      'Lunas',
                      currencyFormat.format(_paidBilling),
                      const Color(0xFF10B981),
                    ),
                    const SizedBox(width: 8),
                    _summaryChip(
                      'Belum',
                      currencyFormat.format(_unpaidBilling),
                      const Color(0xFFEF4444),
                    ),
                  ],
                ),

              const SizedBox(height: 8),

              // Status Filter
              Row(
                children: [
                  _filterChip('Semua', null),
                  const SizedBox(width: 8),
                  _filterChip('Belum Lunas', 'unpaid'),
                  const SizedBox(width: 8),
                  _filterChip('Lunas', 'paid'),
                ],
              ),
            ],
          ),
        ),

        // Invoice List
        Expanded(
          child: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        _error!,
                        style: TextStyle(color: Colors.red.shade300),
                      ),
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: _loadInvoices,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : _invoices.isEmpty
              ? const Center(
                  child: Text(
                    'Tidak ada tagihan.',
                    style: TextStyle(color: Colors.white38),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadInvoices,
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: _invoices.length,
                    itemBuilder: (context, index) {
                      final inv = _invoices[index];
                      return InvoiceTile(
                        invoice: inv,
                        currencyFormat: currencyFormat,
                        onPay: inv.isPaid ? null : () => _payInvoice(inv),
                      );
                    },
                  ),
                ),
        ),
      ],
    );
  }

  Widget _summaryChip(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
        decoration: BoxDecoration(
          color: const Color(0xFF1E293B),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Text(
              label,
              style: const TextStyle(color: Colors.white38, fontSize: 10),
            ),
            const SizedBox(height: 2),
            FittedBox(
              child: Text(
                value,
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _filterChip(String label, String? status) {
    final isActive = _statusFilter == status;
    return GestureDetector(
      onTap: () {
        setState(() => _statusFilter = status);
        _loadInvoices();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isActive ? const Color(0xFF3B82F6) : const Color(0xFF1E293B),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isActive
                ? const Color(0xFF3B82F6)
                : Colors.white.withValues(alpha: 0.1),
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isActive ? Colors.white : Colors.white54,
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
        ),
      ),
    );
  }
}
