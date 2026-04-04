import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

enum LegalContentType { privacy, terms, website }

class LegalContentScreen extends StatelessWidget {
  final LegalContentType type;

  const LegalContentScreen({
    super.key,
    required this.type,
  });

  @override
  Widget build(BuildContext context) {
    final title = type == LegalContentType.privacy
        ? 'Privacy Policy'
        : type == LegalContentType.terms
            ? 'Terms of Service'
            : 'Official Website';

    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text(title, style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
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
                'Last Updated: March 10, 2026',
                style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary),
              ),
              const SizedBox(height: 24),
              if (type == LegalContentType.privacy)
                ..._buildPrivacyContent()
              else if (type == LegalContentType.terms)
                ..._buildTermsContent()
              else
                ..._buildWebsiteContent(),
              const SizedBox(height: 48),
            ],
          ),
        ),
      ),
    );
  }

  List<Widget> _buildPrivacyContent() {
    return [
      _sectionTitle('1. Information Collection'),
      _bodyText('PharmVR collects minimal personal data necessary for your training progress. This includes your name, email address, and academic/institutional affiliation provided during registration.'),
      _sectionTitle('2. VR and Spatial Data'),
      _bodyText('We prioritize your physical privacy. PharmVR does not collect or store spatial mapping data, room configurations, or biometric data from your Quest 3 headset. Environment processing happens locally on your device.'),
      _sectionTitle('3. AI Assistant Conversations'),
      _bodyText('Conversations with PharmAI are processed to provide accurate pharmaceutical guidance. Conversations are anonymized and used exclusively to improve the AI\'s pedagogical accuracy concerning CPOB/GMP standards.'),
      _sectionTitle('4. Usage Data'),
      _bodyText('We track module completion rates, assessment scores, and VR session durations to generate your training analytics and certificates.'),
      _sectionTitle('5. Data Security'),
      _bodyText('All data is encrypted in transit and at rest. We do not sell your personal information to third parties.'),
    ];
  }

  List<Widget> _buildTermsContent() {
    return [
      _sectionTitle('1. Acceptance of Terms'),
      _bodyText('By accessing PharmVR, you agree to be bound by these Terms of Service. This platform is intended for pharmaceutical education and professional training.'),
      _sectionTitle('2. Proper Use'),
      _bodyText('You agree to use the VR simulations and AI assistant exclusively for their intended educational purposes. Harassment, abuse, or attempts to bypass security protocols are strictly prohibited.'),
      _sectionTitle('3. Intellectual Property'),
      _bodyText('All training modules, VR environments, and CPOB specialized content are the intellectual property of PharmVR. Users are granted a non-exclusive license for personal educational use.'),
      _sectionTitle('4. AI Disclaimer'),
      _bodyText('While PharmAI is trained on high-quality pharmaceutical standards, its responses should be used as a learning aid. Critical manufacturing decisions should always refer to official BPOM or international GMP documentation.'),
      _sectionTitle('5. Account Responsibility'),
      _bodyText('You are responsible for maintaining the confidentiality of your credentials and for all activities that occur under your account.'),
    ];
  }

  List<Widget> _buildWebsiteContent() {
    return [
      _sectionTitle('Website Address'),
      _bodyText('https://pharmvr.com'),
      const SizedBox(height: 16),
      _sectionTitle('Office Address'),
      _bodyText('Jakarta, Indonesia'),
      _bodyText('Pharmaceutical District, Block VR-Quest'),
      const SizedBox(height: 16),
      _sectionTitle('Contact Information'),
      _bodyText('Email: support@pharmvr.com'),
      _bodyText('Phone: +62 21 555 1234'),
      const SizedBox(height: 32),
      _bodyText('PharmVR provides immersive virtual reality learning experiences specifically designed for Good Manufacturing Practice (GMP/CPOB) excellence in the pharmaceutical industry.'),
    ];
  }

  Widget _sectionTitle(String text) {
    return Padding(
      padding: const EdgeInsets.only(top: 24, bottom: 8),
      child: Text(
        text,
        style: PharmTextStyles.h4.copyWith(color: PharmColors.primary),
      ),
    );
  }

  Widget _bodyText(String text) {
    return Text(
      text,
      style: PharmTextStyles.bodyMedium.copyWith(
        color: PharmColors.textSecondary,
        height: 1.6,
      ),
    );
  }
}
