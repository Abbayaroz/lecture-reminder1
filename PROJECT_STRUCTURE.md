# IKCOE Lecture Timetable Reminder System - Project Structure

## Directory Layout

```
ikcoe-lecture-reminder/
├── backend/
│   ├── laravel/
│   │   ├── app/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   ├── Middleware/
│   │   │   │   └── Requests/
│   │   │   ├── Models/
│   │   │   ├── Services/
│   │   │   ├── Jobs/
│   │   │   └── Events/
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   ├── seeders/
│   │   │   └── factories/
│   │   ├── routes/
│   │   ├── config/
│   │   └── .env.example
│   └── storage/
├── frontend/
│   ├── flutter/
│   │   ├── lib/
│   │   │   ├── main.dart
│   │   │   ├── src/
│   │   │   │   ├── app/
│   │   │   │   ├── data/
│   │   │   │   ├── presentation/
│   │   │   │   └── domain/
│   │   │   └── utils/
│   │   ├── android/
│   │   ├── ios/
│   │   ├── pubspec.yaml
│   │   └── test/
│   └── assets/
├── docs/
│   ├── API_DOCUMENTATION.md
│   ├── DATABASE_SCHEMA.md
│   ├── DEPLOYMENT_GUIDE.md
│   └── TESTING.md
└── README.md
```

## Tech Stack

- **Frontend**: Flutter 3.x
- **Backend**: Laravel 10.x
- **Database**: MySQL 8.0+
- **Authentication**: JWT (JSON Web Tokens)
- **Notifications**: Firebase Cloud Messaging (FCM)
- **Storage**: Firebase Storage & Local Storage
- **Email**: SMTP
- **QR Code**: qrcode_flutter, qr_code_scanner

## Project Modules

1. **Authentication Module**
   - User Registration
   - Login/Logout
   - Password Management
   - Email Verification
   - JWT Token Management

2. **User Management Module**
   - Student Management
   - Lecturer Management
   - Administrator Management
   - Timetable Officer Management
   - Profile Management

3. **Academic Structure Module**
   - Faculties Management
   - Departments Management
   - Programmes Management
   - Courses Management
   - Levels Management

4. **Timetable Module**
   - Timetable Creation
   - Timetable Management
   - Lecture Scheduling
   - Conflict Resolution
   - Export Functionality

5. **Notification Module**
   - Push Notifications
   - Email Notifications
   - SMS Notifications
   - Scheduled Reminders
   - Notification Management

6. **Attendance Module**
   - QR Code Generation
   - Attendance Marking
   - Attendance Reports
   - Attendance Statistics

7. **Dashboard & Analytics Module**
   - Admin Dashboard
   - Lecturer Dashboard
   - Student Dashboard
   - Analytics & Reports
   - Export Reports

8. **Settings Module**
   - Profile Settings
   - Notification Preferences
   - Theme Settings
   - Security Settings
   - Language Settings