import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/notification_provider.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({Key? key}) : super(key: key);

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    context.read<NotificationProvider>().fetchNotifications();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          PopupMenuButton(
            itemBuilder: (context) => [
              PopupMenuItem(
                child: const Text('Mark all as read'),
                onTap: () {
                  context
                      .read<NotificationProvider>()
                      .fetchNotifications();
                },
              ),
              PopupMenuItem(
                child: const Text('Delete all'),
                onTap: () {
                  context.read<NotificationProvider>().deleteNotification(0);
                },
              ),
            ],
          ),
        ],
      ),
      body: Consumer<NotificationProvider>(
        builder: (context, notificationProvider, _) {
          if (notificationProvider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (notificationProvider.notifications.isEmpty) {
            return const Center(
              child: Text('No notifications'),
            );
          }

          return ListView.builder(
            itemCount: notificationProvider.notifications.length,
            itemBuilder: (context, index) {
              final notification =
                  notificationProvider.notifications[index];
              return ListTile(
                leading: Icon(
                  notification.isRead ? Icons.mail_outline : Icons.mail,
                ),
                title: Text(notification.title),
                subtitle: Text(notification.message),
                trailing: PopupMenuButton(
                  itemBuilder: (context) => [
                    PopupMenuItem(
                      child: const Text('Delete'),
                      onTap: () {
                        context
                            .read<NotificationProvider>()
                            .deleteNotification(notification.id);
                      },
                    ),
                  ],
                ),
                onTap: () {
                  if (!notification.isRead) {
                    context
                        .read<NotificationProvider>()
                        .markAsRead(notification.id);
                  }
                },
              );
            },
          );
        },
      ),
    );
  }
}
