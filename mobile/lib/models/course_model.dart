class CourseModel {
  final int id;
  final int departmentId;
  final String code;
  final String title;
  final String description;
  final double creditUnits;
  final String courseType;
  final bool isActive;

  CourseModel({
    required this.id,
    required this.departmentId,
    required this.code,
    required this.title,
    required this.description,
    required this.creditUnits,
    required this.courseType,
    required this.isActive,
  });

  factory CourseModel.fromJson(Map<String, dynamic> json) {
    return CourseModel(
      id: json['id'],
      departmentId: json['department_id'],
      code: json['code'],
      title: json['title'],
      description: json['description'] ?? '',
      creditUnits: (json['credit_units'] ?? 0).toDouble(),
      courseType: json['course_type'] ?? '',
      isActive: json['is_active'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'department_id': departmentId,
      'code': code,
      'title': title,
      'description': description,
      'credit_units': creditUnits,
      'course_type': courseType,
      'is_active': isActive,
    };
  }
}
