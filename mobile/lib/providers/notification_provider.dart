import 'package:flutter/material.dart';
import '../models/notification_model.dart';
import '../services/api_service.dart';

class NotificationProvider extends ChangeNotifier {
  List<NotificationModel> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;
  String? _error;

  List<NotificationModel> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;
  String? get error => _error;

  final ApiService _apiService = ApiService();

  Future<void> fetchNotifications({int page = 1}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.get('/notifications',
          queryParameters: {'page': page.toString(), 'per_page': '20'});

      if (response['success']) {
        _notifications = (response['data']['data'] as List)
            .map((n) => NotificationModel.fromJson(n))
            .toList();
      } else {
        _error = response['message'] ?? 'Failed to fetch notifications';
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchUnreadNotifications() async {
    try {
      final response = await _apiService.get('/notifications/unread');

      if (response['success']) {
        _unreadCount = response['count'] ?? 0;
        notifyListeners();
      }
    } catch (e) {
      print('Error fetching unread: $e');
    }
  }

  Future<void> markAsRead(int notificationId) async {
    try {
      final response = await _apiService.post('/notifications/$notificationId/read', {});

      if (response['success']) {
        final index = _notifications.indexWhere((n) => n.id == notificationId);
        if (index != -1) {
          _unreadCount = (_unreadCount - 1).clamp(0, double.infinity).toInt();
          notifyListeners();
        }
      }
    } catch (e) {
      print('Error marking as read: $e');
    }
  }

  Future<void> deleteNotification(int notificationId) async {
    try {
      final response = await _apiService.delete('/notifications/$notificationId');

      if (response['success']) {
        _notifications.removeWhere((n) => n.id == notificationId);
        notifyListeners();
      }
    } catch (e) {
      print('Error deleting notification: $e');
    }
  }
}
