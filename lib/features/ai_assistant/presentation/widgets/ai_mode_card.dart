import 'package:flutter/material.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';

class AiModeCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final bool isSelected;
  final VoidCallback onTap;

  const AiModeCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: isSelected 
              ? PharmColors.primary.withOpacity(0.1) 
              : Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: isSelected 
                ? PharmColors.primary 
                : Theme.of(context).dividerColor.withOpacity(0.5),
              width: isSelected ? 2 : 1,
            ),
            boxShadow: [
              if (isSelected)
                BoxShadow(
                  color: PharmColors.primary.withOpacity(0.3),
                  blurRadius: 15,
                  spreadRadius: -2,
                ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: isSelected 
                    ? PharmColors.primary 
                    : Theme.of(context).scaffoldBackgroundColor,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  icon,
                  color: isSelected ? Colors.white : PharmColors.primary,
                  size: 24,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title.toUpperCase(),
                      style: PharmTextStyles.label.copyWith(
                        color: isSelected ? PharmColors.primary : Theme.of(context).textTheme.bodyLarge?.color,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 1.0,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: PharmTextStyles.caption.copyWith(
                        color: Theme.of(context).textTheme.bodyMedium?.color?.withOpacity(0.5),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
              if (isSelected)
                const Icon(
                  Icons.check_circle_rounded,
                  color: PharmColors.primary,
                  size: 20,
                ),
            ],
          ),
        ),
      ),
    );
  }
}
