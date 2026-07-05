import 'package:flutter/material.dart';
import '../models/timetable_model.dart';
import '../services/api_service.dart';

class TimetableProvider extends ChangeNotifier {
  List<TimetableModel> _timetables = [];
  List<TimetableModel> _upcomingTimetables = [];
  bool _isLoading = false;
  String? _error;

  List<TimetableModel> get timetables => _timetables;
  List<TimetableModel> get upcomingTimetables => _upcomingTimetables;
  bool get isLoading => _isLoading;
  String? get error => _error;

  final ApiService _apiService = ApiService();

  Future<void> fetchTimetables({String? day, int? levelId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final query = {'per_page': 100};
      if (day != null) query['day'] = day;
      if (levelId != null) query['level_id'] = levelId.toString();

      final response = await _apiService.get('/timetables', queryParameters: query);

      if (response['success']) {
        _timetables = (response['data']['data'] as List)
            .map((t) => TimetableModel.fromJson(t))
            .toList();
      } else {
        _error = response['message'] ?? 'Failed to fetch timetables';
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchUpcomingTimetables() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.get('/timetables/upcoming');

      if (response['success']) {
        _upcomingTimetables = (response['data'] as List)
            .map((t) => TimetableModel.fromJson(t))
            .toList();
      } else {
        _error = response['message'] ?? 'Failed to fetch upcoming timetables';
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchTimetableByDay(String day) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.get('/timetables/by-day',
          queryParameters: {'day': day, 'per_page': 50});

      if (response['success']) {
        _timetables = (response['data']['data'] as List)
            .map((t) => TimetableModel.fromJson(t))
            .toList();
      } else {
        _error = response['message'] ?? 'Failed to fetch timetables';
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }
}
