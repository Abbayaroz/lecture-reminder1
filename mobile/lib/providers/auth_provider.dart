import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/auth_model.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  AuthModel? _user;
  String? _token;
  bool _isLoading = false;
  String? _error;

  AuthModel? get user => _user;
  String? get token => _token;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _token != null;

  final ApiService _apiService = ApiService();
  final Future<SharedPreferences> _prefs = SharedPreferences.getInstance();

  AuthProvider() {
    _loadFromStorage();
  }

  Future<void> _loadFromStorage() async {
    final prefs = await _prefs;
    _token = prefs.getString('auth_token');
    final userJson = prefs.getString('user');
    if (userJson != null) {
      // _user = AuthModel.fromJson(jsonDecode(userJson));
    }
    notifyListeners();
  }

  Future<bool> register({
    required String firstName,
    required String lastName,
    required String email,
    required String phone,
    required String password,
    required String role,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.post('/auth/register', {
        'first_name': firstName,
        'last_name': lastName,
        'email': email,
        'phone': phone,
        'password': password,
        'password_confirmation': password,
        'role': role,
      });

      if (response['success']) {
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response['message'] ?? 'Registration failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response =
          await _apiService.post('/auth/login', {'email': email, 'password': password});

      if (response['success']) {
        _token = response['token'];
        _user = AuthModel.fromJson(response['user']);

        final prefs = await _prefs;
        await prefs.setString('auth_token', _token!);
        // await prefs.setString('user', jsonEncode(_user!.toJson()));

        _apiService.setToken(_token!);
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response['message'] ?? 'Login failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await _apiService.post('/auth/logout', {});
      _token = null;
      _user = null;

      final prefs = await _prefs;
      await prefs.remove('auth_token');
      await prefs.remove('user');

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
