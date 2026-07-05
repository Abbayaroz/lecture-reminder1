import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/timetable_provider.dart';
import '../../providers/notification_provider.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  void _loadData() {
    context.read<TimetableProvider>().fetchUpcomingTimetables();
    context.read<NotificationProvider>().fetchUnreadNotifications();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Lecture Reminder'),
        actions: [
          IconButton(
            icon: Badge.count(
              count: context.watch<NotificationProvider>().unreadCount,
              child: const Icon(Icons.notifications),
            ),
            onPressed: () {
              Navigator.of(context).pushNamed('/notifications');
            },
          ),
          IconButton(
            icon: const Icon(Icons.person),
            onPressed: () {
              Navigator.of(context).pushNamed('/profile');
            },
          ),
        ],
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: [
          _buildDashboard(),
          _buildTimetable(),
          _buildCourses(),
        ],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.schedule),
            label: 'Timetable',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.book),
            label: 'Courses',
          ),
        ],
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
    );
  }

  Widget _buildDashboard() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Consumer<AuthProvider>(
            builder: (context, authProvider, _) {
              return Text(
                'Welcome, ${authProvider.user?.fullName ?? 'User'}',
                style: Theme.of(context).textTheme.headlineSmall,
              );
            },
          ),
          const SizedBox(height: 24),
          Text(
            'Upcoming Lectures',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          const SizedBox(height: 12),
          Consumer<TimetableProvider>(
            builder: (context, timetableProvider, _) {
              if (timetableProvider.isLoading) {
                return const Center(child: CircularProgressIndicator());
              }

              if (timetableProvider.upcomingTimetables.isEmpty) {
                return const Center(
                  child: Text('No upcoming lectures'),
                );
              }

              return ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: timetableProvider.upcomingTimetables.length,
                itemBuilder: (context, index) {
                  final timetable =
                      timetableProvider.upcomingTimetables[index];
                  return Card(
                    child: ListTile(
                      leading: const Icon(Icons.school),
                      title: Text(
                        timetable.courseAllocation?['course']?['title'] ??
                            'Course',
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${timetable.dayOfWeek.toUpperCase()} ${timetable.startTime} - ${timetable.endTime}',
                          ),
                          Text(
                            timetable.lectureHall?['name'] ?? 'TBA',
                          ),
                        ],
                      ),
                    ),
                  );
                },
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildTimetable() {
    return Consumer<TimetableProvider>(
      builder: (context, timetableProvider, _) {
        if (timetableProvider.isLoading) {
          return const Center(child: CircularProgressIndicator());
        }

        if (timetableProvider.timetables.isEmpty) {
          return const Center(
            child: Text('No timetables available'),
          );
        }

        return ListView.builder(
          padding: const EdgeInsets.all(16),
          itemCount: timetableProvider.timetables.length,
          itemBuilder: (context, index) {
            final timetable = timetableProvider.timetables[index];
            return Card(
              child: ListTile(
                title: Text(
                  timetable.courseAllocation?['course']?['title'] ??
                      'Course',
                ),
                subtitle: Text(
                  '${timetable.dayOfWeek} ${timetable.startTime}',
                ),
                trailing: Chip(
                  label: Text(timetable.status),
                  backgroundColor: timetable.status == 'scheduled'
                      ? Colors.green
                      : Colors.red,
                ),
                onTap: () {
                  Navigator.of(context).pushNamed(
                    '/timetable-detail',
                    arguments: timetable,
                  );
                },
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildCourses() {
    return const Center(
      child: Text('Courses'),
    );
  }
}
