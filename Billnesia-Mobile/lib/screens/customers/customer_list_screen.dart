import 'package:flutter/material.dart';
import '../../models/customer.dart';
import '../../services/api_service.dart';
import '../../widgets/customer_tile.dart';
import 'customer_detail_screen.dart';

class CustomerListScreen extends StatefulWidget {
  const CustomerListScreen({super.key});

  @override
  State<CustomerListScreen> createState() => _CustomerListScreenState();
}

class _CustomerListScreenState extends State<CustomerListScreen> {
  List<Customer> _customers = [];
  bool _isLoading = true;
  String? _error;
  String _search = '';
  final _searchController = TextEditingController();
  int _currentPage = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    _loadCustomers();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadCustomers({int page = 1}) async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final params = <String, String>{
        'page': page.toString(),
        'per_page': '20',
      };
      if (_search.isNotEmpty) params['search'] = _search;

      final result = await ApiService.get('/customers', params: params);
      if (result['success'] == true) {
        final data = result['data'];
        final list = (data['data'] as List)
            .map((e) => Customer.fromJson(e))
            .toList();
        setState(() {
          _customers = list;
          _currentPage = data['current_page'] ?? 1;
          _lastPage = data['last_page'] ?? 1;
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
        _error = 'Gagal memuat data pelanggan.';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Search Bar
        Padding(
          padding: const EdgeInsets.all(16),
          child: TextField(
            controller: _searchController,
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              hintText: 'Cari pelanggan...',
              hintStyle: const TextStyle(color: Colors.white38),
              prefixIcon: const Icon(Icons.search, color: Colors.white38),
              suffixIcon: _search.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear, color: Colors.white38),
                      onPressed: () {
                        _searchController.clear();
                        setState(() => _search = '');
                        _loadCustomers();
                      },
                    )
                  : null,
              filled: true,
              fillColor: const Color(0xFF1E293B),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide.none,
              ),
            ),
            onSubmitted: (v) {
              setState(() => _search = v);
              _loadCustomers();
            },
          ),
        ),

        // Customer List
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
                        onPressed: _loadCustomers,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : _customers.isEmpty
              ? const Center(
                  child: Text(
                    'Tidak ada pelanggan.',
                    style: TextStyle(color: Colors.white38),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: () => _loadCustomers(page: _currentPage),
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: _customers.length + 1, // +1 for pagination
                    itemBuilder: (context, index) {
                      if (index == _customers.length) {
                        return _buildPagination();
                      }
                      final customer = _customers[index];
                      return CustomerTile(
                        customer: customer,
                        onTap: () async {
                          await Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) =>
                                  CustomerDetailScreen(customerId: customer.id),
                            ),
                          );
                          _loadCustomers(page: _currentPage);
                        },
                      );
                    },
                  ),
                ),
        ),
      ],
    );
  }

  Widget _buildPagination() {
    if (_lastPage <= 1) return const SizedBox();
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          IconButton(
            onPressed: _currentPage > 1
                ? () => _loadCustomers(page: _currentPage - 1)
                : null,
            icon: const Icon(Icons.chevron_left, color: Colors.white54),
          ),
          Text(
            'Halaman $_currentPage / $_lastPage',
            style: const TextStyle(color: Colors.white54, fontSize: 13),
          ),
          IconButton(
            onPressed: _currentPage < _lastPage
                ? () => _loadCustomers(page: _currentPage + 1)
                : null,
            icon: const Icon(Icons.chevron_right, color: Colors.white54),
          ),
        ],
      ),
    );
  }
}
