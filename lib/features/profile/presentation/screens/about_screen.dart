import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/widgets/pharm_loading_indicator.dart';
import '../providers/app_setting_provider.dart';
import '../../domain/models/app_setting.dart';

/// A premium informational screen explaining PharmVR's identity,
/// mission, and version metadata.
class AboutPharmVrScreenNew extends ConsumerWidget {
  const AboutPharmVrScreenNew({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settingsAsync = ref.watch(appSettingProvider);

    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text('About PharmVR', style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
        backgroundColor: PharmColors.surface,
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: PharmColors.primary),
          onPressed: () => context.pop(),
        ),
      ),
      body: SafeArea(
        child: settingsAsync.when(
          loading: () => const Center(child: PharmLoadingIndicator()),
          error: (err, stack) => Center(child: Text('Error: $err', style: const TextStyle(color: Colors.red))),
          data: (settings) => RefreshIndicator(
            onRefresh: () => ref.read(appSettingProvider.notifier).refresh(),
            child: SingleChildScrollView(
              padding: PharmSpacing.allLg,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const SizedBox(height: 24),
                  // Hero Section
                  _buildHeroSection(),
                  const SizedBox(height: 48),

                  // Description
                  _buildContentSection(settings),
                  const SizedBox(height: 48),

                  // Links
                  _buildLinksSection(context, settings),
                  const SizedBox(height: 32),

                  // Metadata footer
                  _buildMetadataFooter(),
                  const SizedBox(height: 48),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeroSection() {
    return Column(
      children: [
        // App Logo presentation with glow
        Container(
          width: 120,
          height: 120,
          decoration: BoxDecoration(
            color: PharmColors.surfaceLight,
            shape: BoxShape.circle,
            border: Border.all(color: PharmColors.primary.withOpacity(0.3), width: 2),
            boxShadow: [
              BoxShadow(
                color: PharmColors.primary.withOpacity(0.2),
                blurRadius: 32,
                spreadRadius: 8,
              ),
            ],
          ),
          child: const Center(
            child: Icon(
              Icons.view_in_ar,
              color: PharmColors.primary,
              size: 56,
            ),
          ),
        ),
        const SizedBox(height: 24),
        Text(
          'PharmVR',
          style: PharmTextStyles.h1.copyWith(
            color: PharmColors.textPrimary,
            letterSpacing: 1.5,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
          decoration: BoxDecoration(
            color: PharmColors.primary.withOpacity(0.1),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: PharmColors.primary.withOpacity(0.2)),
          ),
          child: Text(
            'Virtual Reality for CPOB Learning',
            style: PharmTextStyles.subtitle.copyWith(
              color: PharmColors.primary,
              letterSpacing: 0.5,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildContentSection(AppSetting settings) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: PharmColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: PharmColors.cardBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.psychology_outlined, color: PharmColors.primary),
              const SizedBox(width: 12),
              Text('OUR MISSION', style: PharmTextStyles.overline.copyWith(color: PharmColors.textSecondary)),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            settings.aboutMission.isNotEmpty 
                ? settings.aboutMission 
                : 'PharmVR is a next-generation VR-centered learning platform designed specifically for the pharmaceutical industry.',
            style: PharmTextStyles.bodyLarge.copyWith(
              color: PharmColors.textPrimary,
              height: 1.6,
            ),
          ),
          const SizedBox(height: 16),
          Text(
            settings.aboutDescription.isNotEmpty
                ? settings.aboutDescription
                : 'By merging Good Manufacturing Practice (GMP/CPOB) standards with immersive VR simulations and an intelligent AI assistant, PharmVR bridges the gap between theoretical knowledge and practical training.',
            style: PharmTextStyles.bodyMedium.copyWith(
              color: PharmColors.textSecondary,
              height: 1.6,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLinksSection(BuildContext context, AppSetting settings) {
    return Column(
      children: [
        _buildLinkTile(
          icon: Icons.policy_outlined,
          title: 'Privacy Policy',
          onTap: () => _launchURL(settings.privacyPolicyUrl),
        ),
        _buildDivider(),
        _buildLinkTile(
          icon: Icons.gavel_outlined,
          title: 'Terms of Service',
          onTap: () => _launchURL(settings.termsOfServiceUrl),
        ),
        _buildDivider(),
        _buildLinkTile(
          icon: Icons.language_outlined,
          title: 'Official Website',
          onTap: () => _launchURL(settings.officialWebsiteUrl),
        ),
      ],
    );
  }

  Future<void> _launchURL(String? urlString) async {
    if (urlString == null || urlString.isEmpty) return;
    final Uri url = Uri.parse(urlString);
    if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
      debugPrint('Could not launch $url');
    }
  }

  Widget _buildLinkTile({required IconData icon, required String title, required VoidCallback onTap}) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          child: Row(
            children: [
              Icon(icon, color: PharmColors.textSecondary, size: 22),
              const SizedBox(width: 16),
              Expanded(
                child: Text(
                  title,
                  style: PharmTextStyles.bodyLarge.copyWith(color: PharmColors.textPrimary),
                ),
              ),
              const Icon(Icons.arrow_forward_ios, color: PharmColors.textTertiary, size: 16),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDivider() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Divider(height: 1, color: PharmColors.divider.withOpacity(0.5)),
    );
  }

  Widget _buildMetadataFooter() {
    return Column(
      children: [
        Text(
          'Version 1.0.0 (Build 34)',
          style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary),
        ),
        const SizedBox(height: 4),
        Text(
          '© ${DateTime.now().year} PharmVR. All rights reserved.',
          style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary),
        ),
      ],
    );
  }
}
