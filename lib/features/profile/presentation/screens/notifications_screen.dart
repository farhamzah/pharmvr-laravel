import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

/// A premium, organized screen for configuring notification preferences.
/// Future-ready for backend sync via Riverpod.
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  // Local state for UI toggles. Will eventually be driven by Riverpod state.
  bool _assessmentReminders = true;
  bool _vrSessionReminders = true;
  bool _trainingCompletion = true;
  bool _newsUpdates = false;
  bool _gmpUpdates = true;
  bool _securityAlerts = true;
  bool _accountActivity = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text('Notifications', style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
        backgroundColor: PharmColors.surface,
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: PharmColors.primary),
          onPressed: () => context.pop(),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: PharmSpacing.allLg,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Stay updated with your VR training progress and important alerts. You can change these preferences at any time.',
                style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.5),
              ),
              const SizedBox(height: 32),

              _buildSection(
                title: 'TRAINING NOTIFICATIONS',
                items: [
                  _ToggleItem(
                    title: 'Assessment Reminders',
                    subtitle: 'Notifications for upcoming pre-tests and post-tests.',
                    value: _assessmentReminders,
                    onChanged: (v) => setState(() => _assessmentReminders = v),
                  ),
                  _buildDivider(),
                  _ToggleItem(
                    title: 'VR Session Reminders',
                    subtitle: 'Alerts before your scheduled VR simulation begins.',
                    value: _vrSessionReminders,
                    onChanged: (v) => setState(() => _vrSessionReminders = v),
                  ),
                  _buildDivider(),
                  _ToggleItem(
                    title: 'Training Completion',
                    subtitle: 'Summaries and rewards when you finish a module.',
                    value: _trainingCompletion,
                    onChanged: (v) => setState(() => _trainingCompletion = v),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              _buildSection(
                title: 'CONTENT NOTIFICATIONS',
                items: [
                  _ToggleItem(
                    title: 'News Updates',
                    subtitle: 'Latest articles and pharmaceutical news.',
                    value: _newsUpdates,
                    onChanged: (v) => setState(() => _newsUpdates = v),
                  ),
                  _buildDivider(),
                  _ToggleItem(
                    title: 'GMP/CPOB Updates',
                    subtitle: 'Crucial industry regulation changes.',
                    value: _gmpUpdates,
                    onChanged: (v) => setState(() => _gmpUpdates = v),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              _buildSection(
                title: 'SYSTEM NOTIFICATIONS',
                items: [
                  _ToggleItem(
                    title: 'Security Alerts',
                    subtitle: 'Unrecognized logins or password changes.',
                    value: _securityAlerts,
                    onChanged: (v) => setState(() => _securityAlerts = v),
                  ),
                  _buildDivider(),
                  _ToggleItem(
                    title: 'Account Activity',
                    subtitle: 'Profile updates and system messages.',
                    value: _accountActivity,
                    onChanged: (v) => setState(() => _accountActivity = v),
                  ),
                ],
              ),
              const SizedBox(height: 48),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSection({required String title, required List<Widget> items}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 12),
          child: Text(
            title,
            style: PharmTextStyles.overline.copyWith(color: PharmColors.textTertiary, letterSpacing: 1.5),
          ),
        ),
        Container(
          decoration: BoxDecoration(
            color: PharmColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: PharmColors.cardBorder),
          ),
          child: Column(
            children: items,
          ),
        ),
      ],
    );
  }

  Widget _buildDivider() {
    return Divider(height: 1, indent: 16, endIndent: 16, color: PharmColors.divider.withOpacity(0.5));
  }
}

class _ToggleItem extends StatelessWidget {
  final String title;
  final String subtitle;
  final bool value;
  final ValueChanged<bool> onChanged;

  const _ToggleItem({
    required this.title,
    required this.subtitle,
    required this.value,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => onChanged(!value),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: PharmTextStyles.bodyLarge.copyWith(color: PharmColors.textPrimary)),
                    const SizedBox(height: 4),
                    Text(subtitle, style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary, height: 1.4)),
                  ],
                ),
              ),
              const SizedBox(width: 16),
              Switch.adaptive(
                value: value,
                onChanged: onChanged,
                activeColor: PharmColors.primary,
                activeTrackColor: PharmColors.primary.withOpacity(0.3),
                inactiveThumbColor: PharmColors.textTertiary,
                inactiveTrackColor: PharmColors.surfaceLight,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
