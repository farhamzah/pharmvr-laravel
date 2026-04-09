import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/utils/responsive_helper.dart';
import '../../../profile/presentation/providers/app_setting_provider.dart';

class LandingScreen extends ConsumerStatefulWidget {
  const LandingScreen({super.key});

  @override
  ConsumerState<LandingScreen> createState() => _LandingScreenState();
}

class _LandingScreenState extends ConsumerState<LandingScreen> {
  late ScrollController _scrollController;
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  double _scrollOpacity = 0;

  @override
  void initState() {
    super.initState();
    _scrollController = ScrollController();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    final offset = _scrollController.offset;
    final opacity = (offset / 100).clamp(0.0, 1.0);
    if (opacity != _scrollOpacity) {
      setState(() {
        _scrollOpacity = opacity;
      });
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final settingsAsync = ref.watch(appSettingProvider);
    final isDesktop = ResponsiveHelper.isDesktop(context);
    final isTablet = ResponsiveHelper.isTablet(context);
    final isMobile = ResponsiveHelper.isMobile(context);

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: PharmColors.background,
      endDrawer: !isDesktop ? _MobileDrawer() : null, // Add simple drawer
      body: Stack(
        children: [
          Positioned.fill(
            child: SingleChildScrollView(
              controller: _scrollController,
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                children: [
                  const SizedBox(height: 100), // Space for header
                  _HeroSection(isDesktop: isDesktop, isTablet: isTablet, isMobile: isMobile),
                  _DashboardPreviewSection(isDesktop: isDesktop),
                  settingsAsync.when(
                    data: (settings) => _AboutSection(
                      mission: settings.aboutMission,
                      description: settings.aboutDescription,
                      isDesktop: isDesktop,
                    ),
                    loading: () => const SizedBox(height: 200),
                    error: (_, __) => const SizedBox(height: 200),
                  ),
                  _TutorialSection(isDesktop: isDesktop),
                  _FooterSection(isDesktop: isDesktop),
                ],
              ),
            ),
          ),
          Positioned(
            top: 0, left: 0, right: 0,
            child: _LandingHeader(
              isDesktop: isDesktop, 
              opacity: _scrollOpacity,
            ),
          ),
        ],
      ),
    );
  }
}

class _MobileDrawer extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: PharmColors.background,
      child: Container(
        padding: const EdgeInsets.all(32),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Image.asset('assets/images/Pharmvrlogo.png', height: 40),
                IconButton(
                  icon: const Icon(Icons.close, color: PharmColors.textPrimary),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const SizedBox(height: 60),
            _DrawerLink(label: 'ABOUT', onTap: () => Navigator.pop(context)),
            _DrawerLink(label: 'TUTORIAL', onTap: () => Navigator.pop(context)),
            _DrawerLink(label: 'CONTACT', onTap: () => Navigator.pop(context)),
            const Spacer(),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  context.push('/auth/login');
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: PharmColors.primary,
                  foregroundColor: Colors.black,
                  padding: const EdgeInsets.symmetric(vertical: 18),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('LOGIN', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DrawerLink extends StatelessWidget {
  final String label;
  final VoidCallback onTap;
  const _DrawerLink({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 32),
      child: GestureDetector(
        onTap: onTap,
        child: Text(
          label,
          style: PharmTextStyles.h3.copyWith(
            color: PharmColors.textPrimary,
            letterSpacing: 1.2,
          ),
        ),
      ),
    );
  }
}

class _LandingHeader extends StatelessWidget {
  final bool isDesktop;
  final double opacity;
  const _LandingHeader({required this.isDesktop, required this.opacity});

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      child: BackdropFilter(
        filter: ui.ImageFilter.blur(
          sigmaX: 10 * opacity, 
          sigmaY: 10 * opacity,
        ),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: EdgeInsets.symmetric(
            horizontal: isDesktop ? 80 : 20,
            vertical: 16,
          ),
          decoration: BoxDecoration(
            color: PharmColors.background.withOpacity(opacity * 0.8),
            border: Border(
              bottom: BorderSide(
                color: PharmColors.divider.withOpacity(opacity * 0.5),
                width: 1,
              ),
            ),
          ),
          child: Row(
            children: [
              Image.asset(
                'assets/images/Pharmvrlogo.png',
                height: 40,
              ),
              const SizedBox(width: 12),
              Text(
                'PharmVR',
                style: PharmTextStyles.h2.copyWith(
                  color: PharmColors.textPrimary,
                  letterSpacing: 2.0,
                  fontSize: isDesktop ? 22 : 18,
                ),
              ),
              const Spacer(),
              if (isDesktop) ...[
                _HeaderLink(label: 'ABOUT', onTap: () {}),
                const SizedBox(width: 32),
                _HeaderLink(label: 'TUTORIAL', onTap: () {}),
                const SizedBox(width: 32),
                _HeaderLink(label: 'CONTACT', onTap: () {}),
                const SizedBox(width: 40),
                ElevatedButton(
                  onPressed: () => context.push('/auth/login'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: PharmColors.primary,
                    foregroundColor: PharmColors.background,
                    padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    elevation: 0,
                  ),
                  child: const Text('LOGIN', style: TextStyle(fontWeight: FontWeight.bold, letterSpacing: 1.2)),
                ),
              ] else 
                IconButton(
                  icon: const Icon(Icons.menu_rounded, color: PharmColors.textPrimary, size: 28),
                  onPressed: () => Scaffold.of(context).openEndDrawer(),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class _HeaderLink extends StatelessWidget {
  final String label;
  final VoidCallback onTap;
  const _HeaderLink({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return TextButton(
      onPressed: onTap,
      child: Text(
        label,
        style: PharmTextStyles.bodyMedium.copyWith(
          color: PharmColors.textSecondary,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _HeroSection extends StatelessWidget {
  final bool isDesktop;
  final bool isTablet;
  final bool isMobile;
  const _HeroSection({
    required this.isDesktop,
    required this.isTablet,
    required this.isMobile,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: isDesktop ? 700 : (isTablet ? 600 : 550),
      width: double.infinity,
      padding: EdgeInsets.symmetric(horizontal: isDesktop ? 80 : 20),
      child: Stack(
        children: [
          // Background Glow/Visual
          Positioned.fill(
            child: Opacity(
              opacity: 0.5,
              child: Image.asset(
                'assets/images/web_landing_hero.png',
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(
                  decoration: BoxDecoration(
                    gradient: RadialGradient(
                      colors: [
                        PharmColors.primary.withOpacity(0.2),
                        PharmColors.background,
                      ],
                      radius: 1.2,
                    ),
                  ),
                ),
              ),
            ),
          ),
          // Subtle Dark Layer for better readability
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    PharmColors.background.withOpacity(0.8),
                    PharmColors.background.withOpacity(0.4),
                    PharmColors.background.withOpacity(0.8),
                  ],
                ),
              ),
            ),
          ),
          Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                TweenAnimationBuilder<double>(
                  tween: Tween(begin: 0, end: 1),
                  duration: const Duration(seconds: 1),
                  builder: (context, value, child) {
                    return Opacity(
                      opacity: value,
                      child: Transform.translate(
                        offset: Offset(0, 20 * (1 - value)),
                        child: child,
                      ),
                    );
                  },
                  child: Column(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                        decoration: BoxDecoration(
                          color: PharmColors.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(30),
                          border: Border.all(color: PharmColors.primary.withOpacity(0.3)),
                        ),
                        child: Text(
                          'FUTURE OF PHARMACEUTICAL TRAINING',
                          style: PharmTextStyles.overline.copyWith(
                            color: PharmColors.primary,
                            letterSpacing: 2.0,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      const SizedBox(height: 32),
                      Text(
                        isDesktop 
                            ? 'Immersive CPOB Learning \nPowered by VR & AI' 
                            : (isTablet ? 'Immersive CPOB Learning \nPlatform' : 'Immersive CPOB \nLearning Platform'),
                        textAlign: TextAlign.center,
                        style: PharmTextStyles.h1.copyWith(
                          color: PharmColors.textPrimary,
                          fontSize: isDesktop ? 72 : (isTablet ? 56 : 32),
                          height: 1.1,
                          letterSpacing: -1.0,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Container(
                        constraints: BoxConstraints(
                          maxWidth: isDesktop ? 800 : (isTablet ? 600 : double.infinity),
                        ),
                        child: Text(
                          'Empower your team with cutting-edge Virtual Reality simulations \nand intelligent diagnostics for modern pharmaceutical excellence.',
                          textAlign: TextAlign.center,
                          style: PharmTextStyles.bodyLarge.copyWith(
                            color: PharmColors.textSecondary,
                            height: 1.6,
                            fontSize: isDesktop ? 18 : 16,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 64),
                Wrap(
                  spacing: 20,
                  runSpacing: 20,
                  alignment: WrapAlignment.center,
                  children: [
                    ElevatedButton(
                      onPressed: () => context.push('/auth/register'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: PharmColors.background,
                        padding: EdgeInsets.symmetric(
                          horizontal: isDesktop ? 48 : 32, 
                          vertical: isDesktop ? 24 : 18
                        ),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      ),
                      child: Text(
                        'GET STARTED NOW', 
                        style: TextStyle(
                          fontSize: isDesktop ? 16 : 14, 
                          fontWeight: FontWeight.w900, 
                          letterSpacing: 1.5
                        )
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _DashboardPreviewSection extends StatelessWidget {
  final bool isDesktop;
  const _DashboardPreviewSection({required this.isDesktop});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isDesktop ? 80 : 20,
        vertical: 140,
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            decoration: BoxDecoration(
              color: PharmColors.primary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              'PLATFORM CAPABILITIES',
              style: PharmTextStyles.overline.copyWith(color: PharmColors.primary),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'Advanced Analytics Dashboard',
            style: PharmTextStyles.h2.copyWith(
              color: PharmColors.textPrimary,
              fontSize: isDesktop ? 48 : 32,
            ),
          ),
          const SizedBox(height: 16),
          Container(
            constraints: BoxConstraints(
              maxWidth: isDesktop ? 600 : double.infinity,
            ),
            child: Text(
              'Track your progress, manage modules, and analyze VR performance results with our integrated intelligence suite.',
              textAlign: TextAlign.center,
              style: PharmTextStyles.bodyLarge.copyWith(
                color: PharmColors.textSecondary,
                fontSize: isDesktop ? 18 : 16,
              ),
            ),
          ),
          const SizedBox(height: 80),
          // Glassmorphic Preview Card
          Container(
            constraints: BoxConstraints(
              maxWidth: isDesktop ? 1100 : double.infinity,
            ),
            width: double.infinity,
            height: isDesktop ? 600 : (ResponsiveHelper.isTablet(context) ? 500 : 400),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(40),
              border: Border.all(color: Colors.white.withOpacity(0.15)),
              boxShadow: [
                BoxShadow(
                  color: PharmColors.primary.withOpacity(0.1),
                  blurRadius: 60,
                  spreadRadius: 10,
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(40),
              child: Stack(
                children: [
                  // Abstract Background Pattern within Card
                  Positioned.fill(
                    child: Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [
                            PharmColors.surface.withOpacity(0.9),
                            PharmColors.surfaceLight.withOpacity(0.7),
                          ],
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    top: -100, right: -100,
                    child: Container(
                      width: 300, height: 300,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: PharmColors.primary.withOpacity(0.05),
                      ),
                    ),
                  ),
                  
                  // Mock Dashboard UI Elements
                  Positioned(
                    top: isDesktop ? 60 : 30, 
                    left: isDesktop ? 60 : 20,
                    child: _MockStatCard(
                      label: 'TOTAL XP', 
                      value: '12,450', 
                      icon: Icons.diamond_rounded,
                      color: PharmColors.primary,
                      isDesktop: isDesktop,
                    ),
                  ),
                  Positioned(
                    top: isDesktop ? 180 : (ResponsiveHelper.isTablet(context) ? 130 : 120), 
                    left: isDesktop ? 60 : 20,
                    child: _MockStatCard(
                      label: 'MODULES COMPLETED', 
                      value: '8/12', 
                      icon: Icons.library_books_rounded,
                      color: Colors.purpleAccent,
                      isDesktop: isDesktop,
                    ),
                  ),
                  Positioned(
                    top: isDesktop ? 300 : (ResponsiveHelper.isTablet(context) ? 230 : 210), 
                    left: isDesktop ? 60 : 20,
                    child: _MockStatCard(
                      label: 'VR ACCURACY', 
                      value: '94.2%', 
                      icon: Icons.track_changes_rounded,
                      color: Colors.greenAccent,
                      isDesktop: isDesktop,
                    ),
                  ),
                  
                  if (isDesktop) Positioned(
                    top: 60, right: 60, bottom: 60,
                    child: _MockActivityGraph(),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _MockStatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  final bool isDesktop;
  const _MockStatCard({
    required this.label, 
    required this.value, 
    required this.icon,
    this.color = PharmColors.primary,
    this.isDesktop = true,
  });

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(20),
      child: BackdropFilter(
        filter: ui.ImageFilter.blur(sigmaX: 5, sigmaY: 5),
        child: Container(
          width: isDesktop ? 280 : 240,
          padding: EdgeInsets.all(isDesktop ? 24 : 16),
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.05),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.white.withOpacity(0.1)),
          ),
          child: Row(
            children: [
              Container(
                padding: EdgeInsets.all(isDesktop ? 12 : 8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(15),
                ),
                child: Icon(icon, color: color, size: isDesktop ? 28 : 22),
              ),
              SizedBox(width: isDesktop ? 20 : 12),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label, 
                    style: PharmTextStyles.overline.copyWith(
                      color: PharmColors.textTertiary,
                      letterSpacing: 1.5,
                      fontSize: isDesktop ? 12 : 10,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    value, 
                    style: PharmTextStyles.h3.copyWith(
                      color: PharmColors.textPrimary,
                      fontSize: isDesktop ? 24 : 18,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _MockActivityGraph extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: 400,
      height: 250,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: PharmColors.surfaceLight.withOpacity(0.8),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: PharmColors.primary.withOpacity(0.1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Learning Progress', style: PharmTextStyles.subtitle.copyWith(color: PharmColors.textPrimary)),
          const Spacer(),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: List.generate(7, (index) => Container(
              width: 12,
              height: 40.0 + (index * 20.0 % 100),
              decoration: BoxDecoration(
                color: PharmColors.primary.withOpacity(0.6),
                borderRadius: BorderRadius.circular(4),
              ),
            )),
          ),
        ],
      ),
    );
  }
}

class _AboutSection extends StatelessWidget {
  final String mission;
  final String description;
  final bool isDesktop;
  const _AboutSection({required this.mission, required this.description, required this.isDesktop});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.symmetric(
        horizontal: isDesktop ? 80 : 20,
        vertical: 140,
      ),
      decoration: BoxDecoration(
        color: PharmColors.surface.withOpacity(0.4),
        border: Border.symmetric(
          horizontal: BorderSide(color: PharmColors.divider.withOpacity(0.3)),
        ),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 40, height: 2,
                decoration: BoxDecoration(
                  gradient: LinearGradient(colors: [Colors.transparent, PharmColors.primary]),
                ),
              ),
              const SizedBox(width: 16),
              Text(
                'ABOUT PHARMVR', 
                style: PharmTextStyles.overline.copyWith(
                  color: PharmColors.primary,
                  letterSpacing: 3.0,
                ),
              ),
              const SizedBox(width: 16),
              Container(
                width: 40, height: 2,
                decoration: BoxDecoration(
                  gradient: LinearGradient(colors: [PharmColors.primary, Colors.transparent]),
                ),
              ),
            ],
          ),
          const SizedBox(height: 48),
          Container(
            constraints: BoxConstraints(
              maxWidth: isDesktop ? 900 : double.infinity,
            ),
            child: Text(
              mission.isNotEmpty ? mission : 'Bridging the gap between theoretical knowledge and practical training for pharmaceutical excellence.',
              textAlign: TextAlign.center,
              style: PharmTextStyles.h2.copyWith(
                color: PharmColors.textPrimary, 
                height: 1.3,
                fontSize: isDesktop ? 42 : 24,
              ),
            ),
          ),
          const SizedBox(height: 32),
          Container(
            constraints: BoxConstraints(
              maxWidth: isDesktop ? 800 : double.infinity,
            ),
            child: Text(
              description.isNotEmpty ? description : 'PharmVR is a next-generation immersion platform designed to train the next generation of pharmaceutical professionals using VR & AI.',
              textAlign: TextAlign.center,
              style: PharmTextStyles.bodyLarge.copyWith(
                color: PharmColors.textSecondary, 
                height: 1.8,
                fontSize: isDesktop ? 18 : 14,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TutorialSection extends StatelessWidget {
  final bool isDesktop;
  const _TutorialSection({required this.isDesktop});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isDesktop ? 80 : 20,
        vertical: 140,
      ),
      child: Column(
        children: [
          Text(
            'How It Works', 
            style: PharmTextStyles.h2.copyWith(
              color: PharmColors.textPrimary,
              fontSize: 40,
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'Three simple steps to master pharmaceutical procedures.',
            style: PharmTextStyles.bodyLarge.copyWith(color: PharmColors.textSecondary),
          ),
          const SizedBox(height: 80),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40),
            child: Wrap(
              spacing: 60,
              runSpacing: 60,
              alignment: WrapAlignment.center,
              children: [
                _TutorialStep(
                  number: '01',
                  title: 'Login & Select Module',
                  desc: 'Access your dedicated dashboard and pick from a wide range of CPOB certified training modules.',
                  icon: Icons.login_rounded,
                  iconColor: PharmColors.primary,
                ),
                _TutorialStep(
                  number: '02',
                  title: 'Learn & Sync VR',
                  desc: 'Review materials on any device, then sync with your Meta Quest 3 headset for immersive simulation.',
                  icon: Icons.vrpano_rounded,
                  iconColor: Colors.purpleAccent,
                ),
                _TutorialStep(
                  number: '03',
                  title: 'Assess & Certify',
                  desc: 'Complete assessments after training to track your mastery and gain official certifications.',
                  icon: Icons.verified_user_rounded,
                  iconColor: Colors.greenAccent,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _TutorialStep extends StatelessWidget {
  final String number;
  final String title;
  final String desc;
  final IconData icon;
  final Color iconColor;
  const _TutorialStep({
    required this.number, 
    required this.title, 
    required this.desc, 
    required this.icon,
    this.iconColor = PharmColors.primary,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 280,
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: PharmColors.surface,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: PharmColors.cardBorder),
        boxShadow: [
          BoxShadow(
            color: iconColor.withOpacity(0.05),
            blurRadius: 20,
            offset: const Offset(0, 10),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(number, style: PharmTextStyles.h4.copyWith(color: iconColor.withOpacity(0.5))),
              const Spacer(),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: iconColor, size: 24),
              ),
            ],
          ),
          const SizedBox(height: 24),
          Text(title, style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
          const SizedBox(height: 12),
          Text(desc, style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.5)),
        ],
      ),
    );
  }
}

class _FooterSection extends StatelessWidget {
  final bool isDesktop;
  const _FooterSection({required this.isDesktop});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isDesktop ? 80 : 20,
        vertical: 100,
      ),
      decoration: BoxDecoration(
        color: PharmColors.surface,
        border: Border(top: BorderSide(color: PharmColors.divider.withOpacity(0.5))),
      ),
      child: Column(
        children: [
          if (isDesktop) Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                flex: 2,
                child: _FooterBrandSection(),
              ),
              Expanded(
                child: _FooterPlatformSection(),
              ),
              Expanded(
                child: _FooterLegalSection(),
              ),
              Expanded(
                child: _FooterContactSection(),
              ),
            ],
          ) else Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _FooterBrandSection(),
              const SizedBox(height: 48),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(child: _FooterPlatformSection()),
                  Expanded(child: _FooterLegalSection()),
                ],
              ),
              const SizedBox(height: 48),
              _FooterContactSection(),
            ],
          ),
          const SizedBox(height: 80),
          const Divider(color: PharmColors.cardBorder),
          const SizedBox(height: 40),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '© ${DateTime.now().year} PharmVR Pro. All rights reserved.',
                style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary),
              ),
              Row(
                children: [
                  _SocialIcon(icon: Icons.language),
                  const SizedBox(width: 20),
                  _SocialIcon(icon: Icons.info_outline),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _FooterBrandSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Image.asset('assets/images/Pharmvrlogo.png', height: 40),
            const SizedBox(width: 12),
            Text(
              'PharmVR',
              style: PharmTextStyles.h3.copyWith(
                color: PharmColors.textPrimary,
                letterSpacing: 1.5,
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),
        Text(
          'Revolutionizing pharmaceutical training through \nimmersive Virtual Reality and Intelligent AI diagnostics.',
          style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.8),
        ),
      ],
    );
  }
}

class _FooterPlatformSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('PLATFORM', style: PharmTextStyles.label.copyWith(color: PharmColors.textPrimary, fontWeight: FontWeight.bold)),
        const SizedBox(height: 24),
        _FooterLink(label: 'Experience VR'),
        _FooterLink(label: 'Modules Catalog'),
        _FooterLink(label: 'Intelligence Hub'),
      ],
    );
  }
}

class _FooterLegalSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('LEGAL', style: PharmTextStyles.label.copyWith(color: PharmColors.textPrimary, fontWeight: FontWeight.bold)),
        const SizedBox(height: 24),
        _FooterLink(label: 'Privacy Policy'),
        _FooterLink(label: 'Terms of Service'),
        _FooterLink(label: 'Cookie Policy'),
      ],
    );
  }
}

class _FooterContactSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('CONTACT', style: PharmTextStyles.label.copyWith(color: PharmColors.textPrimary, fontWeight: FontWeight.bold)),
        const SizedBox(height: 24),
        Row(
          children: [
            const Icon(Icons.email_outlined, color: PharmColors.primary, size: 16),
            const SizedBox(width: 12),
            Text('support@pharmvr.cloud', style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary)),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            const Icon(Icons.location_on_outlined, color: PharmColors.primary, size: 16),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                'Universitas Padjadjaran, Bandung, Indonesia', 
                style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary)
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _SocialIcon extends StatelessWidget {
  final IconData icon;
  const _SocialIcon({required this.icon});

  @override
  Widget build(BuildContext context) {
    return Icon(icon, color: PharmColors.textTertiary, size: 20);
  }
}

class _FooterLink extends StatelessWidget {
  final String label;
  const _FooterLink({required this.label});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text(
        label,
        style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary),
      ),
    );
  }
}
