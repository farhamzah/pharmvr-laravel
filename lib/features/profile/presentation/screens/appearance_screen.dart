import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/theme_provider.dart';

/// A premium screen for selecting the application appearance/theme.
/// It emphasizes the Dark futuristic theme as the flagship PharmVR experience.
class AppearanceScreen extends ConsumerStatefulWidget {
  const AppearanceScreen({super.key});

  @override
  ConsumerState<AppearanceScreen> createState() => _AppearanceScreenState();
}

class _AppearanceScreenState extends ConsumerState<AppearanceScreen> {
  void _handleThemeSelect(ThemeMode mode) {
    ref.read(themeProvider.notifier).setThemeMode(mode);
    
    // Show feedback
    ScaffoldMessenger.of(context).clearSnackBars();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Tampilan diperbarui ke ${mode == ThemeMode.light ? "Light Mode" : mode == ThemeMode.dark ? "Dark Mode" : "Default Sistem"}', 
          style: PharmTextStyles.bodyMedium.copyWith(color: Colors.white),
        ),
        backgroundColor: PharmColors.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final themeMode = ref.watch(themeProvider);

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: const Text('Appearance'),
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
              // Recommendation Banner
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: PharmColors.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: PharmColors.primary.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.visibility_outlined, color: PharmColors.primary, size: 28),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Text(
                        'PharmVR is designed for a premium Dark immersive experience. Light mode is only for high-contrast accessibility.',
                        style: PharmTextStyles.caption.copyWith(color: PharmColors.primary, height: 1.5),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),
              
              Text(
                'THEME',
                style: PharmTextStyles.overline.copyWith(color: PharmColors.textTertiary, letterSpacing: 1.5),
              ),
              const SizedBox(height: 12),
              
              _ThemeOptionCard(
                title: 'System Default',
                description: 'Match your device settings automatically.',
                icon: Icons.brightness_auto,
                isSelected: themeMode == ThemeMode.system,
                onTap: () => _handleThemeSelect(ThemeMode.system),
              ),
              const SizedBox(height: 16),
              
              _ThemeOptionCard(
                title: 'Dark Mode (Flagship)',
                description: 'Premium futuristic aesthetic. Default and recommended.',
                icon: Icons.dark_mode_outlined,
                isSelected: themeMode == ThemeMode.dark,
                onTap: () => _handleThemeSelect(ThemeMode.dark),
                isFlagship: true,
              ),
              const SizedBox(height: 16),
              
              _ThemeOptionCard(
                title: 'Light Mode',
                description: 'High contrast standard visibility.',
                icon: Icons.light_mode_outlined,
                isSelected: themeMode == ThemeMode.light,
                onTap: () => _handleThemeSelect(ThemeMode.light),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ThemeOptionCard extends StatelessWidget {
  final String title;
  final String description;
  final IconData icon;
  final bool isSelected;
  final bool isFlagship;
  final VoidCallback onTap;

  const _ThemeOptionCard({
    required this.title,
    required this.description,
    required this.icon,
    required this.isSelected,
    required this.onTap,
    this.isFlagship = false,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      decoration: BoxDecoration(
        color: isSelected 
            ? PharmColors.primary.withValues(alpha: 0.08) 
            : Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isSelected 
              ? PharmColors.primary 
              : (Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
          width: isSelected ? 2 : 1,
        ),
        boxShadow: isSelected
            ? [
                BoxShadow(
                  color: PharmColors.primary.withValues(alpha: 0.15),
                  blurRadius: 16,
                  spreadRadius: 2,
                )
              ]
            : [],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: isSelected || isFlagship
                        ? PharmColors.primary.withValues(alpha: 0.15)
                        : (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight),
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Icon(
                      icon,
                      color: isSelected || isFlagship
                          ? PharmColors.primary
                          : Theme.of(context).textTheme.labelSmall?.color,
                      size: 24,
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              title,
                              style: PharmTextStyles.h3.copyWith(
                                color: isSelected ? PharmColors.primary : Theme.of(context).textTheme.displaySmall?.color,
                                fontSize: 16,
                              ),
                            ),
                          ),
                          if (isFlagship && !isSelected)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: PharmColors.primary.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                'PRO',
                                style: PharmTextStyles.caption.copyWith(
                                  color: PharmColors.primary,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 10,
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        description,
                        style: PharmTextStyles.caption.copyWith(
                          color: Theme.of(context).textTheme.labelSmall?.color,
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(width: 16),
                if (isSelected)
                  const Icon(
                    Icons.check_circle,
                    color: PharmColors.primary,
                    size: 24,
                  )
                else
                  Icon(
                    Icons.circle_outlined,
                    color: Theme.of(context).textTheme.labelSmall?.color?.withValues(alpha: 0.5),
                    size: 24,
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
