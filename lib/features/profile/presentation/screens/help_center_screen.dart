import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

/// A premium structured screen for user support and FAQs.
/// Expandable categories allow easy scanning without overwhelming the user.
class HelpCenterScreen extends StatelessWidget {
  const HelpCenterScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text('Help Center', style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
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
              // Intro
              Text(
                'How can we help you?',
                style: PharmTextStyles.h2.copyWith(color: PharmColors.textPrimary),
              ),
              const SizedBox(height: 8),
              Text(
                'Find answers to common questions about connecting to VR, taking assessments, and using your AI assistant.',
                style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.5),
              ),
              const SizedBox(height: 32),

              // Categories
              _buildFaqSection(
                title: 'ACCOUNT & LOGIN',
                items: [
                  const _FaqItem(
                    question: 'How do I log in?',
                    answer: 'Use the email and password registered by your institution. If you haven\'t received credentials, contact your lab administrator.',
                  ),
                  const _FaqItem(
                    question: 'How do I reset my password?',
                    answer: 'Navigate to "Profile > Account > Change Password" to update it inside the app. For forgotten passwords during login, tap "Forgot Password" on the login screen to receive a reset link.',
                  ),
                ],
              ),
              const SizedBox(height: 24),

              _buildFaqSection(
                title: 'TRAINING & ASSESSMENT',
                items: [
                  const _FaqItem(
                    question: 'How do I start a pre-test?',
                    answer: 'Go to the Dashboard or Education tab, select a module, and tap the "Start Pre-Test" button. You must pass this before unlocking the VR module.',
                  ),
                  const _FaqItem(
                    question: 'Where can I find my test results?',
                    answer: 'Your post-test results and overall progress are summarized on your Dashboard in the "Training Journey" section.',
                  ),
                ],
              ),
              const SizedBox(height: 24),

              _buildFaqSection(
                title: 'VR CONNECTION & USAGE',
                items: [
                  const _FaqItem(
                    question: 'How do I connect to the VR Headset?',
                    answer: 'Tap the headset icon in the top right of your Dashboard. Make sure both your mobile device and your Quest headset are on the same Wi-Fi network.',
                  ),
                  const _FaqItem(
                    question: 'The VR module isn\'t launching',
                    answer: 'Ensure your headset is marked as "Connected" in the app, and that you have successfully completed the required pre-test for that module.',
                  ),
                ],
              ),
              const SizedBox(height: 24),

              _buildFaqSection(
                title: 'AI ASSISTANT & CONTENT',
                items: [
                  const _FaqItem(
                    question: 'What does the AI Assistant help with?',
                    answer: 'Our AI is trained specifically on GMP/CPOB guidelines. You can ask it for clarifications on pharmaceutical standards, module definitions, or general theory help.',
                  ),
                  const _FaqItem(
                    question: 'Where can I find GMP/CPOB materials?',
                    answer: 'Navigate to the "Education" tab to read full chapters and latest guidelines regarding sterile and non-sterile manufacturing.',
                  ),
                ],
              ),
              const SizedBox(height: 40),

              // Contact Support Button
              Center(
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
                  decoration: BoxDecoration(
                    color: PharmColors.primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: PharmColors.primary.withValues(alpha: 0.2)),
                  ),
                  child: Column(
                    children: [
                      const Icon(Icons.support_agent, color: PharmColors.primary, size: 32),
                      const SizedBox(height: 16),
                      Text(
                        'Still need help?',
                        style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Our lab support team is available during standard operating hours.',
                        textAlign: TextAlign.center,
                        style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary, height: 1.4),
                      ),
                      const SizedBox(height: 16),
                      TextButton(
                        onPressed: () {
                          // Future: launch email client
                        },
                        style: TextButton.styleFrom(
                          foregroundColor: PharmColors.background,
                          backgroundColor: PharmColors.primary,
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: Text(
                          'Contact Support',
                          style: PharmTextStyles.button.copyWith(letterSpacing: 0.5),
                        ),
                      )
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 48),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFaqSection({required String title, required List<Widget> items}) {
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
            children: _buildItemsWithDividers(items),
          ),
        ),
      ],
    );
  }

  List<Widget> _buildItemsWithDividers(List<Widget> items) {
    final result = <Widget>[];
    for (int i = 0; i < items.length; i++) {
      result.add(items[i]);
      if (i < items.length - 1) {
        result.add(Divider(height: 1, indent: 16, endIndent: 16, color: PharmColors.divider.withValues(alpha: 0.5)));
      }
    }
    return result;
  }
}

class _FaqItem extends StatefulWidget {
  final String question;
  final String answer;

  const _FaqItem({required this.question, required this.answer});

  @override
  State<_FaqItem> createState() => _FaqItemState();
}

class _FaqItemState extends State<_FaqItem> with SingleTickerProviderStateMixin {
  bool _isExpanded = false;

  void _toggleExpanded() {
    setState(() {
      _isExpanded = !_isExpanded;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
      child: ExpansionTile(
        title: Text(
          widget.question,
          style: PharmTextStyles.bodyLarge.copyWith(
            color: _isExpanded ? PharmColors.primary : PharmColors.textPrimary,
          ),
        ),
        iconColor: PharmColors.primary,
        collapsedIconColor: PharmColors.textTertiary,
        onExpansionChanged: (expanded) {
          setState(() {
            _isExpanded = expanded;
          });
        },
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Text(
                widget.answer,
                style: PharmTextStyles.bodyMedium.copyWith(
                  color: PharmColors.textSecondary,
                  height: 1.5,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
