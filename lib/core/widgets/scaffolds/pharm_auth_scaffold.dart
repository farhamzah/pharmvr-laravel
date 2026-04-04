import 'package:flutter/material.dart';
import '../../theme/pharm_colors.dart';
import '../../theme/pharm_spacing.dart';

class PharmAuthScaffold extends StatelessWidget {
  final Widget child;
  final String title;
  final String? subtitle;
  final bool showBackButton;

  const PharmAuthScaffold({
    super.key,
    required this.child,
    required this.title,
    this.subtitle,
    this.showBackButton = true,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: showBackButton
          ? AppBar(
              backgroundColor: Colors.transparent,
              elevation: 0,
              leading: IconButton(
                icon: const Icon(Icons.arrow_back_ios_new, color: PharmColors.primary),
                onPressed: () => Navigator.of(context).pop(),
              ),
            )
          : null,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: PharmSpacing.allLg,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.displayMedium,
                  textAlign: TextAlign.center,
                ),
                if (subtitle != null) ...[
                  const SizedBox(height: PharmSpacing.xs),
                  Text(
                    subtitle!,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: PharmColors.textSecondary,
                        ),
                    textAlign: TextAlign.center,
                  ),
                ],
                const SizedBox(height: PharmSpacing.xl),
                child,
              ],
            ),
          ),
        ),
      ),
    );
  }
}
