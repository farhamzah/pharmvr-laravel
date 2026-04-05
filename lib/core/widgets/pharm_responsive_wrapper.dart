import 'package:flutter/material.dart';
import '../theme/pharm_colors.dart';

/// Responsive wrapper that constrains content width on large screens (Web/Desktop).
/// On mobile (<600px), content fills the full width normally.
/// On tablet/desktop (≥600px), content is centered with a max width.
class PharmResponsiveWrapper extends StatelessWidget {
  final Widget child;
  final double maxWidth;
  final bool showSidePanelDecoration;

  const PharmResponsiveWrapper({
    super.key,
    required this.child,
    this.maxWidth = 1100, // Increased for a more professional desktop experience
    this.showSidePanelDecoration = true,
  });

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        // Mobile — pass through unchanged
        if (constraints.maxWidth < 600) {
          return child;
        }

        final isDark = Theme.of(context).brightness == Brightness.dark;

        // Tablet/Desktop — center with max width and premium background
        return Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: isDark
                  ? [const Color(0xFF0A0F14), const Color(0xFF06090D)]
                  : [const Color(0xFFF5F7F9), const Color(0xFFE8ECEF)],
            ),
          ),
          child: Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Main content area — constrained with elegant shadow
                ConstrainedBox(
                  constraints: BoxConstraints(maxWidth: maxWidth),
                  child: Container(
                    decoration: BoxDecoration(
                      color: Theme.of(context).scaffoldBackgroundColor,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(isDark ? 0.3 : 0.08),
                          blurRadius: 50,
                          spreadRadius: 2,
                        ),
                      ],
                      border: Border.symmetric(
                        vertical: BorderSide(
                          color: Theme.of(context).dividerColor.withOpacity(0.05),
                          width: 1,
                        ),
                      ),
                    ),
                    child: child,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
