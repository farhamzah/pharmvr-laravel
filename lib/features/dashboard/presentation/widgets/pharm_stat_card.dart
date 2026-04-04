import 'package:flutter/material.dart';
import '../../../../../core/theme/pharm_colors.dart';
import '../../../../../core/theme/pharm_text_styles.dart';

class PharmStatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color? iconColor;

  const PharmStatCard({
    super.key,
    required this.label,
    required this.value,
    required this.icon,
    this.iconColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: PharmColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: PharmColors.primary.withOpacity(0.05),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 24, color: iconColor ?? PharmColors.primary),
          const Spacer(),
          Text(value, style: PharmTextStyles.h2),
          const SizedBox(height: 2),
          Text(label, style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary)),
        ],
      ),
    );
  }
}
