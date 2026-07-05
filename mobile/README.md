# Lecture Reminder - Mobile App

## Overview
Flutter mobile application for lecture reminder system with timetable management.

## Features
- User authentication (login/register)
- Timetable viewing
- Lecture reminders with push notifications
- Attendance marking via QR code
- Notification management
- Course information

## Getting Started

### Prerequisites
- Flutter SDK 3.0 or higher
- Dart SDK
- Android SDK (for Android development)
- Xcode (for iOS development)

### Installation

1. Install dependencies:
```bash
flutter pub get
```

2. Configure Firebase:
   - Download `google-services.json` for Android
   - Download `GoogleService-Info.plist` for iOS
   - Place them in their respective directories

3. Run the app:
```bash
flutter run
```

### Project Structure
```
lib/
├── main.dart
├── models/
│   ├── auth_model.dart
│   ├── timetable_model.dart
│   ├── notification_model.dart
│   └── course_model.dart
├── providers/
│   ├── auth_provider.dart
│   ├── timetable_provider.dart
│   └── notification_provider.dart
├── screens/
│   ├── auth/
│   │   ├── login_screen.dart
│   │   └── register_screen.dart
│   ├── home/
│   │   └── home_screen.dart
│   ├── notifications/
│   │   └── notifications_screen.dart
│   └── timetable/
│       └── timetable_detail_screen.dart
└── services/
    └── api_service.dart
```

## Architecture
- **State Management**: Provider pattern
- **HTTP Client**: Dio
- **Local Storage**: Shared Preferences
- **Push Notifications**: Firebase Cloud Messaging

## API Configuration
Update `baseUrl` in `lib/services/api_service.dart` to match your backend API endpoint.

## Testing
```bash
flutter test
```

## Build

### Android APK:
```bash
flutter build apk
```

### iOS:
```bash
flutter build ios
```

## Contributing
Follow the project's coding standards and create feature branches for new functionality.
