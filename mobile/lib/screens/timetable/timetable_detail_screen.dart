import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/timetable_model.dart';

class TimetableDetailScreen extends StatelessWidget {
  final TimetableModel timetable;

  const TimetableDetailScreen({Key? key, required this.timetable})
      : super(key: key);

  @override
  Widget build(BuildContext context) {
    final course = timetable.courseAllocation?['course'];
    final lectureHall = timetable.lectureHall;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Lecture Details'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      course?['title'] ?? 'Course',
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      course?['code'] ?? 'N/A',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Schedule',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 12),
            ListTile(
              leading: const Icon(Icons.calendar_today),
              title: const Text('Day'),
              trailing: Text(timetable.dayOfWeek.toUpperCase()),
            ),
            ListTile(
              leading: const Icon(Icons.access_time),
              title: const Text('Time'),
              trailing: Text(
                '${timetable.startTime} - ${timetable.endTime}',
              ),
            ),
            ListTile(
              leading: const Icon(Icons.location_on),
              title: const Text('Venue'),
              trailing: Text(lectureHall?['name'] ?? 'TBA'),
            ),
            ListTile(
              leading: const Icon(Icons.info),
              title: const Text('Status'),
              trailing: Chip(
                label: Text(timetable.status),
                backgroundColor:
                    timetable.status == 'scheduled' ? Colors.green : Colors.red,
              ),
            ),
            if (timetable.cancellationReason != null)
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 16),
                  Text(
                    'Cancellation Reason',
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  const SizedBox(height: 8),
                  Text(timetable.cancellationReason ?? ''),
                ],
              ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Reminder set for this lecture'),
                    ),
                  );
                },
                child: const Text('Set Reminder'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
