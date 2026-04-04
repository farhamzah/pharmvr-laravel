import 'package:flutter/material.dart';
import 'dart:ui';
import '../theme/pharm_colors.dart';

class PharmGlassCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;

  const PharmGlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20.0),
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return ClipRRect(
      borderRadius: BorderRadius.circular(24),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
        child: Container(
          padding: padding,
          decoration: BoxDecoration(
            color: isDark 
                ? PharmColors.surface.withOpacity(0.7)
                : Colors.white.withOpacity(0.6),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(
              color: isDark
                  ? PharmColors.primary.withOpacity(0.1)
                  : PharmColors.primary.withOpacity(0.2),
              width: 1,
            ),
          ),
          child: child,
        ),
      ),
    );
  }
}
