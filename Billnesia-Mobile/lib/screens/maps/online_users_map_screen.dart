import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../../services/api_service.dart';

class OnlineUsersMapScreen extends StatefulWidget {
  const OnlineUsersMapScreen({super.key});

  @override
  State<OnlineUsersMapScreen> createState() => _OnlineUsersMapScreenState();
}

class _OnlineUsersMapScreenState extends State<OnlineUsersMapScreen> {
  List<dynamic> _users = [];
  bool _isLoading = true;
  String? _error;
  final MapController _mapController = MapController();

  @override
  void initState() {
    super.initState();
    _loadMapData();
  }

  Future<void> _loadMapData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final result = await ApiService.get('/maps/online-users');
      if (result['success'] == true) {
        setState(() {
          _users = result['data'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Gagal memuat data map.';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(_error!, style: TextStyle(color: Colors.red.shade300)),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: _loadMapData,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    // Hitung center map berdasarkan titik pertama atau default jika kosong
    LatLng mapCenter = const LatLng(-6.200000, 106.816666); // Default Jakarta
    if (_users.isNotEmpty) {
      final first = _users.first;
      if (first['lat'] != null && first['lng'] != null) {
        double currentLat = first['lat'] is num
            ? (first['lat'] as num).toDouble()
            : double.tryParse(first['lat'].toString()) ?? -6.20;
        double currentLng = first['lng'] is num
            ? (first['lng'] as num).toDouble()
            : double.tryParse(first['lng'].toString()) ?? 106.81;
        mapCenter = LatLng(currentLat, currentLng);
      }
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Peta Pelanggan'),
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              _loadMapData();
            },
          ),
        ],
      ),
      body: FlutterMap(
        mapController: _mapController,
        options: MapOptions(initialCenter: mapCenter, initialZoom: 13.0),
        children: [
          TileLayer(
            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            userAgentPackageName: 'com.billnesia.app',
          ),
          MarkerLayer(
            markers: _users.map((user) {
              final isOnline = user['status'] == 'online';
              return Marker(
                point: LatLng(
                  user['lat'] is num
                      ? (user['lat'] as num).toDouble()
                      : double.tryParse(user['lat'].toString()) ?? 0.0,
                  user['lng'] is num
                      ? (user['lng'] as num).toDouble()
                      : double.tryParse(user['lng'].toString()) ?? 0.0,
                ),
                width: 40,
                height: 40,
                child: GestureDetector(
                  onTap: () {
                    _showUserInfo(context, user);
                  },
                  child: Icon(
                    Icons.location_on,
                    color: isOnline ? Colors.green : Colors.grey,
                    size: 40,
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  void _showUserInfo(BuildContext context, dynamic user) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF1E293B),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 12,
                    height: 12,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: user['status'] == 'online'
                          ? Colors.green
                          : Colors.grey,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    user['name'] ?? 'Unknown',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                'Username: ${user['username'] ?? '-'}',
                style: const TextStyle(color: Colors.white70),
              ),
              const SizedBox(height: 4),
              Text(
                'Telepon: ${user['phone'] ?? '-'}',
                style: const TextStyle(color: Colors.white70),
              ),
              const SizedBox(height: 12),
              const Text(
                'Alamat:',
                style: TextStyle(color: Colors.white54, fontSize: 12),
              ),
              Text(
                user['address'] ?? '-',
                style: const TextStyle(color: Colors.white),
              ),
              const SizedBox(height: 20),
            ],
          ),
        );
      },
    );
  }
}
