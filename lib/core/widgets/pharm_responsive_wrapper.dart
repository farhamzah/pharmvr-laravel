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
    this.maxWidth = 800, // Elevated from 520 for better Detail Page readability
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

        // Tablet/Desktop — center with max width
        return Container(
          color: Theme.of(context).brightness == Brightness.dark 
              ? Theme.of(context).colorScheme.surface.withValues(alpha: 0.5)
              : Theme.of(context).dividerColor.withValues(alpha: 0.05), 
          child: Center(
            child: Row(
              children: [
                // Optional left decoration panel on very wide screens
                if (showSidePanelDecoration && constraints.maxWidth > 1200)
                  Expanded(
                    child: _SideBranding(),
                  ),
                // Main content area — constrained
                ConstrainedBox(
                  constraints: BoxConstraints(maxWidth: maxWidth),
                  child: Container(
                    decoration: BoxDecoration(
                      color: Theme.of(context).scaffoldBackgroundColor,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 30,
                          spreadRadius: 5,
                        ),
                      ],
                      border: Border.symmetric(
                        vertical: BorderSide(
                          color: Theme.of(context).dividerColor.withOpacity(0.1),
                          width: 1,
                        ),
                      ),
                    ),
                    child: child,
                  ),
                ),
                // Optional right panel
                if (showSidePanelDecoration && constraints.maxWidth > 1200)
                  Expanded(
                    child: _SideBranding(isRight: true),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }
}

/// Subtle branding panel shown on desktop sides
class _SideBranding extends StatelessWidget {
  final bool isRight;
  const _SideBranding({this.isRight = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Theme.of(context).brightness == Brightness.dark ? const Color(0xFF060A0E) : Colors.white10,
      child: Center(
        child: Opacity(
          opacity: 0.04, // Refined from 0.06 for more subtle persistence
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                isRight ? Icons.view_in_ar : Icons.vaccines,
                size: 80,
                color: PharmColors.primary,
              ),
              const SizedBox(height: 16),
              Text(
                isRight ? 'VR TRIPLE SIM' : 'PHARM VR',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w900,
                  color: PharmColors.primary,
                  letterSpacing: 8,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
