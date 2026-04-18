import 'dart:convert';

import 'package:http/http.dart' as http;

import 'models.dart';

class ApiClient {
  ApiClient({http.Client? httpClient})
      : _httpClient = httpClient ?? http.Client();

  static const String defaultBaseUrl = String.fromEnvironment(
    'SCIPIONI_API_BASE_URL',
    defaultValue: 'http://localhost:8080/api/v1',
  );

  final http.Client _httpClient;

  Future<List<ApiEvent>> fetchEvents() async {
    final response = await _httpClient.get(
      Uri.parse('$defaultBaseUrl/events'),
      headers: _jsonHeaders,
    );
    final body = _decodeBody(response);
    _ensureSuccess(response.statusCode, body);
    final data = (body['data'] as List<dynamic>? ?? const []);
    return data
        .map((item) => ApiEvent.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  Future<LoginResult> login({
    required String email,
    required String password,
    required String deviceName,
  }) async {
    final response = await _httpClient.post(
      Uri.parse('$defaultBaseUrl/auth/login'),
      headers: _jsonHeaders,
      body: jsonEncode({
        'email': email,
        'password': password,
        'device_name': deviceName,
      }),
    );
    final body = _decodeBody(response);
    _ensureSuccess(response.statusCode, body);
    return LoginResult(
      token: (body['token'] ?? '') as String,
      user: ApiUser.fromJson(body['data'] as Map<String, dynamic>),
    );
  }

  Future<ApiUser> fetchProfile(String token) async {
    final response = await _httpClient.get(
      Uri.parse('$defaultBaseUrl/profile'),
      headers: _authHeaders(token),
    );
    final body = _decodeBody(response);
    _ensureSuccess(response.statusCode, body);
    return ApiUser.fromJson(body['data'] as Map<String, dynamic>);
  }

  Future<List<ApiBooking>> fetchBookings(String token) async {
    final response = await _httpClient.get(
      Uri.parse('$defaultBaseUrl/bookings'),
      headers: _authHeaders(token),
    );
    final body = _decodeBody(response);
    _ensureSuccess(response.statusCode, body);
    final data = (body['data'] as List<dynamic>? ?? const []);
    return data
        .map((item) => ApiBooking.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  Map<String, String> get _jsonHeaders => const {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };

  Map<String, String> _authHeaders(String token) => {
        ..._jsonHeaders,
        'Authorization': 'Bearer $token',
      };

  Map<String, dynamic> _decodeBody(http.Response response) {
    if (response.body.isEmpty) {
      return <String, dynamic>{};
    }

    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  void _ensureSuccess(int statusCode, Map<String, dynamic> body) {
    if (statusCode >= 200 && statusCode < 300) {
      return;
    }

    throw ApiException(
      body['message']?.toString() ?? 'Errore API ($statusCode)',
    );
  }
}

class ApiException implements Exception {
  const ApiException(this.message);

  final String message;

  @override
  String toString() => message;
}
