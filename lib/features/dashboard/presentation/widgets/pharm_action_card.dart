import 'package:flutter/material.dart';
import '../../../../../core/theme/pharm_colors.dart';
import '../../../../../core/theme/pharm_text_styles.dart';

class PharmActionCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final VoidCallback onTap;
  final bool isPrimary;

  const PharmActionCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.onTap,
    this.isPrimary = false,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isPrimary ? PharmColors.primary.withOpacity(0.1) : PharmColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isPrimary ? PharmColors.primary.withOpacity(0.3) : PharmColors.primary.withOpacity(0.05),
            ),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isPrimary ? PharmColors.primary.withOpacity(0.2) : Colors.black12,
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: isPrimary ? PharmColors.primary : PharmColors.textSecondary),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: PharmTextStyles.h3.copyWith(fontSize: 16)),
                    const SizedBox(height: 4),
                    Text(subtitle, style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary)),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios, size: 16, color: PharmColors.textSecondary),
            ],
          ),
        ),
      ),
    );
  }
}
