import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../providers/auth_provider.dart';
import '../../../../core/widgets/pharm_responsive_wrapper.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> with TickerProviderStateMixin {
  late AnimationController _controller;
  late AnimationController _loaderController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2000),
    );

    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.0, 0.5, curve: Curves.easeIn),
      ),
    );

    _scaleAnimation = Tween<double>(begin: 0.8, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.0, 0.5, curve: Curves.easeOutBack),
      ),
    );

    _controller.forward();

    _loaderController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat();

    // 1. Start auth check
    _initAuth();
  }

  Future<void> _initAuth() async {
    debugPrint('DEBUG: SplashScreen _initAuth started');
    // Check local storage for token
    try {
      debugPrint('DEBUG: SplashScreen calling checkAuth()');
      await ref.read(authProvider.notifier).checkAuth();
      debugPrint('DEBUG: SplashScreen checkAuth() completed');
    } catch (e) {
      debugPrint('DEBUG: SplashScreen checkAuth() error: $e');
    }
    
    // Minimum 1s delay for logo animation
    debugPrint('DEBUG: SplashScreen waiting for delay');
    await Future.delayed(const Duration(milliseconds: 1000));
    
    if (!mounted) return;

    final isAuth = ref.read(authProvider).isAuthenticated;
    if (isAuth) {
      context.go('/dashboard');
    } else {
      context.go('/auth/login');
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _loaderController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return PharmResponsiveWrapper(
      child: Scaffold(
        backgroundColor: PharmColors.background,
        body: Stack(
          children: [
            // 1. Ambient Background Layer
            _buildBackground(),
            
            // 2. Main Unified Content
            Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Logo with multi-layered glow
                  Hero(
                    tag: 'splash_logo',
                    child: Container(
                      width: 100,
                      height: 100,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: PharmColors.surface,
                        border: Border.all(
                          color: PharmColors.primary.withOpacity(0.3),
                          width: 2,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: PharmColors.primary.withOpacity(0.15),
                            blurRadius: 40,
                            spreadRadius: 4,
                          ),
                          BoxShadow(
                            color: PharmColors.primary.withOpacity(0.05),
                            blurRadius: 80,
                            spreadRadius: 10,
                          ),
                        ],
                      ),
                      child: Center(
                        child: Image.asset(
                          'assets/images/logo.png',
                          width: 60,
                          height: 60,
                          fit: BoxFit.contain,
                          errorBuilder: (context, error, stackTrace) {
                            return const Icon(
                              Icons.vaccines,
                              size: 44,
                              color: PharmColors.primary,
                            );
                          },
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  
                  // Text Hierarchy - Focused and Premium
                  Text(
                    'PharmVR',
                    style: PharmTextStyles.h1.copyWith(
                      color: PharmColors.primary,
                      letterSpacing: 5,
                      fontSize: 32,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'ADVANCED PHARMA TRAINING',
                    style: PharmTextStyles.overline.copyWith(
                      color: PharmColors.textTertiary.withOpacity(0.9),
                      letterSpacing: 2.5,
                      fontSize: 10,
                    ),
                  ),
                  
                  const SizedBox(height: 42), // Tightened space to loading section
                  
                  // Unified Loading Section - Subtle and Integrated
                  Column(
                    children: [
                      RotationTransition(
                        turns: _loaderController,
                        child: Container(
                          width: 20,
                          height: 20,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: PharmColors.primary.withOpacity(0.12),
                              width: 1.2,
                            ),
                          ),
                          child: const Center(
                            child: Icon(
                              Icons.incomplete_circle_rounded,
                              size: 12,
                              color: PharmColors.primary,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'INITIALIZING PHARMVR EXPERIENCE',
                        style: PharmTextStyles.caption.copyWith(
                          color: PharmColors.textTertiary.withOpacity(0.6),
                          letterSpacing: 1.2,
                          fontSize: 9,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBackground() {
    return Stack(
      children: [
        // 1. Primary Deep Dark Base
        Positioned.fill(
          child: Container(
            color: PharmColors.background,
          ),
        ),
        
        // 2. Extremely Subtle Ambient Glow
        Positioned.fill(
          child: Container(
            decoration: BoxDecoration(
              gradient: RadialGradient(
                center: Alignment.center,
                radius: 1.0,
                colors: [
                  PharmColors.primary.withOpacity(0.03), // Further reduced
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),
        
        // 3. Almost Invisible Tech Grid (Premium Texture Only)
        Positioned.fill(
          child: Opacity(
            opacity: 0.008, // Virtually invisible depth
            child: CustomPaint(
              painter: GridPainter(),
            ),
          ),
        ),
      ],
    );
  }
}

class GridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = PharmColors.primary
      ..strokeWidth = 0.5;

    const double step = 40;

    for (double i = 0; i < size.width; i += step) {
      canvas.drawLine(Offset(i, 0), Offset(i, size.height), paint);
    }

    for (double i = 0; i < size.height; i += step) {
      canvas.drawLine(Offset(0, i), Offset(size.width, i), paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
