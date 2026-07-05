class TimetableModel {
  final int id;
  final int academicSessionId;
  final int courseAllocationId;
  final int lectureHallId;
  final int levelId;
  final String dayOfWeek;
  final String startTime;
  final String endTime;
  final String semester;
  final String status;
  final DateTime? cancelledAt;
  final String? cancellationReason;
  final Map<String, dynamic>? courseAllocation;
  final Map<String, dynamic>? lectureHall;

  TimetableModel({
    required this.id,
    required this.academicSessionId,
    required this.courseAllocationId,
    required this.lectureHallId,
    required this.levelId,
    required this.dayOfWeek,
    required this.startTime,
    required this.endTime,
    required this.semester,
    required this.status,
    this.cancelledAt,
    this.cancellationReason,
    this.courseAllocation,
    this.lectureHall,
  });

  factory TimetableModel.fromJson(Map<String, dynamic> json) {
    return TimetableModel(
      id: json['id'],
      academicSessionId: json['academic_session_id'],
      courseAllocationId: json['course_allocation_id'],
      lectureHallId: json['lecture_hall_id'],
      levelId: json['level_id'],
      dayOfWeek: json['day_of_week'],
      startTime: json['start_time'],
      endTime: json['end_time'],
      semester: json['semester'],
      status: json['status'],
      cancelledAt: json['cancelled_at'] != null
          ? DateTime.parse(json['cancelled_at'])
          : null,
      cancellationReason: json['cancellation_reason'],
      courseAllocation: json['course_allocation'],
      lectureHall: json['lecture_hall'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'academic_session_id': academicSessionId,
      'course_allocation_id': courseAllocationId,
      'lecture_hall_id': lectureHallId,
      'level_id': levelId,
      'day_of_week': dayOfWeek,
      'start_time': startTime,
      'end_time': endTime,
      'semester': semester,
      'status': status,
      'cancelled_at': cancelledAt?.toIso8601String(),
      'cancellation_reason': cancellationReason,
    };
  }
}
