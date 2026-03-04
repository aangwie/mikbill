import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import 'storage_service.dart';

class ApiService {
  /// Build the full URI for an endpoint.
  static Future<Uri> _buildUri(
    String endpoint, {
    Map<String, String>? params,
  }) async {
    final baseUrl = await StorageService.getApiUrl();
    var uri = Uri.parse('$baseUrl${ApiConfig.apiPrefix}$endpoint');
    if (params != null && params.isNotEmpty) {
      uri = uri.replace(queryParameters: params);
    }
    return uri;
  }

  /// Make a GET request to the API.
  static Future<Map<String, dynamic>> get(
    String endpoint, {
    Map<String, String>? params,
  }) async {
    final token = await StorageService.getToken();
    final uri = await _buildUri(endpoint, params: params);

    final response = await http
        .get(uri, headers: _headers(token))
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  /// Make a POST request to the API.
  static Future<Map<String, dynamic>> post(
    String endpoint, {
    Map<String, dynamic>? body,
  }) async {
    final token = await StorageService.getToken();
    final uri = await _buildUri(endpoint);

    final response = await http
        .post(
          uri,
          headers: _headers(token),
          body: body != null ? jsonEncode(body) : null,
        )
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  /// Make a PUT request to the API.
  static Future<Map<String, dynamic>> put(
    String endpoint, {
    Map<String, dynamic>? body,
  }) async {
    final token = await StorageService.getToken();
    final uri = await _buildUri(endpoint);

    final response = await http
        .put(
          uri,
          headers: _headers(token),
          body: body != null ? jsonEncode(body) : null,
        )
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  /// Make a DELETE request to the API.
  static Future<Map<String, dynamic>> delete(String endpoint) async {
    final token = await StorageService.getToken();
    final uri = await _buildUri(endpoint);

    final response = await http
        .delete(uri, headers: _headers(token))
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  static Map<String, String> _headers(String? token) {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  static Map<String, dynamic> _processResponse(http.Response response) {
    final data = jsonDecode(response.body);

    if (response.statusCode == 401) {
      // Token expired or invalid
      StorageService.clearAll();
      throw ApiException('Sesi telah berakhir. Silakan login kembali.', 401);
    }

    if (response.statusCode >= 400) {
      final message = data['message'] ?? 'Terjadi kesalahan.';
      throw ApiException(message, response.statusCode);
    }

    return data;
  }
}

class ApiException implements Exception {
  final String message;
  final int statusCode;

  ApiException(this.message, this.statusCode);

  @override
  String toString() => message;
}
