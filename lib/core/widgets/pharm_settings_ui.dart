import 'package:flutter/material.dart';
import '../theme/pharm_colors.dart';
import '../theme/pharm_text_styles.dart';

/// ═══════════════════════════════════════════════════════════
/// SETTINGS SECTION TITLE
/// ═══════════════════════════════════════════════════════════
class PharmSettingsSectionTitle extends StatelessWidget {
  final String title;

  const PharmSettingsSectionTitle({super.key, required this.title});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 10, top: 20),
      child: Text(
        title.toUpperCase(),
        style: PharmTextStyles.overline.copyWith(
          color: PharmColors.textTertiary,
          letterSpacing: 2,
        ),
      ),
    );
  }
}

/// ═══════════════════════════════════════════════════════════
/// SETTINGS CARD GROUP
/// ═══════════════════════════════════════════════════════════
/// A premium container to group multiple settings items together.
class PharmSettingsCardGroup extends StatelessWidget {
  final List<Widget> children;

  const PharmSettingsCardGroup({super.key, required this.children});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: PharmColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: PharmColors.cardBorder),
      ),
      child: Column(
        children: List.generate(children.length, (index) {
          final isLast = index == children.length - 1;
          return Column(
            children: [
              children[index],
              if (!isLast)
                Divider(
                  height: 1,
                  indent: 56, // Align divider with text, not icon
                  color: PharmColors.divider.withOpacity(0.5),
                ),
            ],
          );
        }),
      ),
    );
  }
}

/// ═══════════════════════════════════════════════════════════
/// STANDARD SETTINGS ITEM (With Chevron)
/// ═══════════════════════════════════════════════════════════
class PharmSettingsItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? subtitle;
  final Widget? trailing; // Optional text or widget before chevron
  final VoidCallback onTap;

  const PharmSettingsItem({
    super.key,
    required this.icon,
    required this.title,
    this.subtitle,
    this.trailing,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          child: Row(
            children: [
              _SettingsIcon(icon: icon),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(title, style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textPrimary)),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(subtitle!, style: PharmTextStyles.caption.copyWith(color: PharmColors.textSecondary)),
                    ],
                  ],
                ),
              ),
              if (trailing != null) ...[
                trailing!,
                const SizedBox(width: 8),
              ],
              const Icon(Icons.arrow_forward_ios, size: 14, color: PharmColors.textTertiary),
            ],
          ),
        ),
      ),
    );
  }
}

/// ═══════════════════════════════════════════════════════════
/// TOGGLE SETTINGS ITEM (Switch)
/// ═══════════════════════════════════════════════════════════
class PharmSettingsToggleItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? subtitle;
  final bool value;
  final ValueChanged<bool> onChanged;

  const PharmSettingsToggleItem({
    super.key,
    required this.icon,
    required this.title,
    this.subtitle,
    required this.value,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          _SettingsIcon(icon: icon, isActive: value),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(title, style: PharmTextStyles.bodyMedium.copyWith(
                  color: value ? PharmColors.textPrimary : PharmColors.textSecondary,
                )),
                if (subtitle != null) ...[
                  const SizedBox(height: 2),
                  Text(subtitle!, style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary)),
                ],
              ],
            ),
          ),
          Switch.adaptive(
            value: value,
            onChanged: onChanged,
            activeColor: PharmColors.primary,
            activeTrackColor: PharmColors.primary.withOpacity(0.3),
            inactiveThumbColor: PharmColors.textSecondary,
            inactiveTrackColor: PharmColors.surfaceLight,
          ),
        ],
      ),
    );
  }
}

/// ═══════════════════════════════════════════════════════════
/// SELECTABLE SETTINGS ITEM (Radio Style)
/// ═══════════════════════════════════════════════════════════
class PharmSettingsSelectableItem extends StatelessWidget {
  final String title;
  final String? subtitle;
  final bool isSelected;
  final VoidCallback onTap;

  const PharmSettingsSelectableItem({
    super.key,
    required this.title,
    this.subtitle,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: isSelected ? PharmColors.primary.withOpacity(0.05) : PharmColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isSelected ? PharmColors.primary.withOpacity(0.5) : PharmColors.cardBorder,
          width: isSelected ? 1.5 : 1.0,
        ),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        title,
                        style: PharmTextStyles.bodyLarge.copyWith(
                          color: isSelected ? PharmColors.primary : PharmColors.textPrimary,
                          fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                        ),
                      ),
                      if (subtitle != null) ...[
                        const SizedBox(height: 4),
                        Text(subtitle!, style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary)),
                      ],
                    ],
                  ),
                ),
                if (isSelected)
                  const Icon(Icons.check_circle, color: PharmColors.primary, size: 24)
                else
                  Icon(Icons.circle_outlined, color: PharmColors.textTertiary.withOpacity(0.5), size: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// ═══════════════════════════════════════════════════════════
/// HELPER NOTE TEXT
/// ═══════════════════════════════════════════════════════════
class PharmHelperNote extends StatelessWidget {
  final String text;

  const PharmHelperNote({super.key, required this.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(Icons.info_outline, size: 16, color: PharmColors.textSecondary.withOpacity(0.7)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              text,
              style: PharmTextStyles.caption.copyWith(
                color: PharmColors.textSecondary.withOpacity(0.8),
                height: 1.5,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// ═══════════════════════════════════════════════════════════
/// INTERNAL HELPER: STYLED SETTINGS ICON
/// ═══════════════════════════════════════════════════════════
class _SettingsIcon extends StatelessWidget {
  final IconData icon;
  final bool isActive;

  const _SettingsIcon({required this.icon, this.isActive = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 38,
      height: 38,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(12),
        color: isActive ? PharmColors.primary.withOpacity(0.1) : PharmColors.surfaceLight,
      ),
      child: Icon(
        icon,
        color: isActive ? PharmColors.primary : PharmColors.textSecondary,
        size: 20,
      ),
    );
  }
}
