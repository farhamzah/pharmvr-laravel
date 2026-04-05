import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/localization/locale_provider.dart';

/// A premium screen for selecting the application language.
/// It uses visually distinct selectable cards with a clear active state.
class LanguageScreen extends ConsumerStatefulWidget {
  const LanguageScreen({super.key});

  @override
  ConsumerState<LanguageScreen> createState() => _LanguageScreenState();
}

class _LanguageScreenState extends ConsumerState<LanguageScreen> {
  final List<Map<String, String>> _supportedLanguages = [
    {
      'code': 'en',
      'name': 'English',
      'nativeName': 'English',
      'region': 'US',
    },
    {
      'code': 'id',
      'name': 'Indonesian',
      'nativeName': 'Bahasa Indonesia',
      'region': 'ID',
    },
  ];

  void _handleLanguageSelect(String code) {
    ref.read(localeProvider.notifier).setLocale(Locale(code));
    
    final l10n = AppLocalizations.of(context)!;
    
    // Demonstrate a brief "saving" interaction visually
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(l10n.languageUpdated, style: PharmTextStyles.bodyMedium.copyWith(color: Colors.white)),
        backgroundColor: PharmColors.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final currentLocale = ref.watch(localeProvider);

    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text(l10n.language, style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
        backgroundColor: PharmColors.surface,
        elevation: 0,
        centerTitle: true,
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
              Text(
                l10n.chooseLanguage,
                style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.5),
              ),
              const SizedBox(height: 32),
              
              ..._supportedLanguages.map((lang) {
                final isSelected = currentLocale.languageCode == lang['code'];
                final localizedName = lang['code'] == 'en' ? l10n.english : l10n.indonesian;
                return Padding(
                  padding: const EdgeInsets.only(bottom: 16),
                  child: _LanguageOptionCard(
                    nativeName: lang['nativeName']!,
                    englishName: localizedName,
                    regionCode: lang['region']!,
                    isSelected: isSelected,
                    onTap: () => _handleLanguageSelect(lang['code']!),
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }
}

class _LanguageOptionCard extends StatelessWidget {
  final String nativeName;
  final String englishName;
  final String regionCode;
  final bool isSelected;
  final VoidCallback onTap;

  const _LanguageOptionCard({
    required this.nativeName,
    required this.englishName,
    required this.regionCode,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      decoration: BoxDecoration(
        color: isSelected ? PharmColors.primary.withOpacity(0.08) : PharmColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isSelected ? PharmColors.primary : PharmColors.cardBorder,
          width: isSelected ? 2 : 1,
        ),
        boxShadow: isSelected
            ? [
                BoxShadow(
                  color: PharmColors.primary.withOpacity(0.15),
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
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                // Region/Flag placeholder circle
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isSelected ? PharmColors.primary.withOpacity(0.15) : PharmColors.surfaceLight,
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Text(
                      regionCode,
                      style: PharmTextStyles.subtitle.copyWith(
                        color: isSelected ? PharmColors.primary : PharmColors.textSecondary,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                
                // Language Names
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        nativeName,
                        style: PharmTextStyles.h3.copyWith(
                          color: isSelected ? PharmColors.primary : PharmColors.textPrimary,
                          fontSize: 18,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        englishName,
                        style: PharmTextStyles.caption.copyWith(
                          color: PharmColors.textTertiary,
                        ),
                      ),
                    ],
                  ),
                ),
                
                // Selection Indicator
                if (isSelected)
                  const Icon(
                    Icons.check_circle,
                    color: PharmColors.primary,
                    size: 24,
                  )
                else
                  Icon(
                    Icons.circle_outlined,
                    color: PharmColors.textTertiary.withOpacity(0.5),
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
