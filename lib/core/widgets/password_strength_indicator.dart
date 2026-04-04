import 'package:flutter/material.dart';
import '../theme/pharm_colors.dart';
import '../theme/pharm_text_styles.dart';

class PasswordStrengthIndicator extends StatelessWidget {
  final String password;

  const PasswordStrengthIndicator({super.key, required this.password});

  double _calculateStrength() {
    if (password.isEmpty) return 0.0;
    double strength = 0.0;
    if (password.length >= 8) strength += 0.25;
    if (RegExp(r'[A-Z]').hasMatch(password)) strength += 0.25;
    if (RegExp(r'[0-9]').hasMatch(password)) strength += 0.25;
    if (RegExp(r'[!@#$%^&*(),.?":{}|<>]').hasMatch(password)) strength += 0.25;
    return strength;
  }

  Color _getColor(double strength) {
    if (strength <= 0.25) return PharmColors.error;
    if (strength <= 0.5) return PharmColors.warning;
    if (strength <= 0.75) return PharmColors.info;
    return PharmColors.success;
  }

  String _getLabel(double strength) {
    if (password.isEmpty) return '';
    if (strength <= 0.25) return 'Sangat Lemah';
    if (strength <= 0.5) return 'Lemah';
    if (strength <= 0.75) return 'Cukup';
    return 'Kuat';
  }

  @override
  Widget build(BuildContext context) {
    final strength = _calculateStrength();
    final color = _getColor(strength);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: strength,
                  backgroundColor: PharmColors.divider.withOpacity(0.5),
                  color: color,
                  minHeight: 4,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Text(
              _getLabel(strength),
              style: PharmTextStyles.caption.copyWith(
                color: color,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ],
    );
  }
}
