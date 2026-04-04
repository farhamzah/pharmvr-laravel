import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/utils/validators.dart';
import '../../../../core/utils/error_handler.dart';
import '../../../../core/widgets/pharm_responsive_wrapper.dart';
import '../providers/auth_provider.dart';
import '../../../../core/widgets/pharm_text_field.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _emailFocus = FocusNode();
  final _passwordFocus = FocusNode();

  bool _rememberMe = true;
  AutovalidateMode _autovalidateMode = AutovalidateMode.disabled;

  late AnimationController _fadeController;
  late Animation<double> _fadeAnim;
  late Animation<Offset> _slideAnim;

  @override
  void initState() {
    super.initState();
    _fadeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
    _fadeAnim = CurvedAnimation(parent: _fadeController, curve: Curves.easeOut);
    _slideAnim = Tween<Offset>(
      begin: const Offset(0, 0.08),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _fadeController, curve: Curves.easeOutCubic));
    _fadeController.forward();
    
    // Clear any previous state on entry with a slight delay
    // to override any OS-level Autofill values
    Future.delayed(const Duration(milliseconds: 100), () {
      if (mounted) _resetForm();
    });
  }

  void _resetForm() {
    if (!mounted) return;
    FocusScope.of(context).unfocus();
    _emailController.text = '';
    _passwordController.text = '';
    _formKey.currentState?.reset();
    setState(() {
      _autovalidateMode = AutovalidateMode.disabled;
      _autovalidateMode = AutovalidateMode.disabled;
    });
    ref.read(authProvider.notifier).resetState();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _emailFocus.dispose();
    _passwordFocus.dispose();
    _fadeController.dispose();
    super.dispose();
  }

  void _handleLogin() async {
    FocusScope.of(context).unfocus();
    setState(() => _autovalidateMode = AutovalidateMode.onUserInteraction);

    if (_formKey.currentState?.validate() ?? false) {
      debugPrint('Attempting login with: [${_emailController.text}] / [${_passwordController.text}]');
      ref.read(authProvider.notifier).clearError();
      await ref.read(authProvider.notifier).login(
            _emailController.text.trim(),
            _passwordController.text,
            _rememberMe,
          );
    }
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<AuthState>(authProvider, (previous, next) {
      if (next.error != null && (previous?.error != next.error)) {
        PharmErrorHandler.showError(
          context,
          PharmErrorHandler.sanitizeError(next.error!),
        );
      } else if (next.isAuthenticated) {
        context.go('/dashboard');
      }
    });

    final authState = ref.watch(authProvider);
    final screenHeight = MediaQuery.of(context).size.height;

    return PharmResponsiveWrapper(
      showSidePanelDecoration: true,
      child: Scaffold(
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        body: Stack(
        children: [
          // ── Background glow orbs ──
          Positioned(
            top: -80,
            right: -60,
            child: Container(
              width: 240,
              height: 240,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    PharmColors.primary.withOpacity(0.08),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -100,
            left: -80,
            child: Container(
              width: 280,
              height: 280,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    PharmColors.primaryDark.withOpacity(0.06),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),

          // ── Main content ──
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
                child: FadeTransition(
                  opacity: _fadeAnim,
                  child: SlideTransition(
                    position: _slideAnim,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        SizedBox(height: screenHeight * 0.02),

                        // ── BRANDING ──
                        _buildBranding(),
                        const SizedBox(height: 40),

                        // ── LOGIN CARD ──
                        _buildLoginCard(authState),
                        const SizedBox(height: 28),

                        // ── BOTTOM HELPER ──
                        _buildBottomHelper(),
                        const SizedBox(height: 16),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════════════════
  // BRANDING SECTION
  // ═══════════════════════════════════════════════════════
  Widget _buildBranding() {
    return Column(
      children: [
        // Logo with subtle glow ring
        Hero(
          tag: 'splash_logo',
          child: Container(
            width: 88,
            height: 88,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: PharmColors.primary.withOpacity(0.1),
                  blurRadius: 40,
                  spreadRadius: 2,
                ),
              ],
            ),
            child: ClipOval(
              child: Image.asset(
                'assets/images/logo.png',
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) => Container(
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Theme.of(context).colorScheme.surface,
                    border: Border.all(
                      color: PharmColors.primary.withOpacity(0.3),
                    ),
                  ),
                  child: const Icon(Icons.vaccines, size: 42, color: PharmColors.primary),
                ),
              ),
            ),
          ),
        ),
        const SizedBox(height: 12),

        // App name
        Text(
          'PharmVR',
          style: PharmTextStyles.h1.copyWith(
            color: PharmColors.primary,
            fontSize: 30,
          ),
        ),
        const SizedBox(height: 4),

        Text(
          'Access your immersive CPOB learning modules',
          style: PharmTextStyles.bodyMedium.copyWith(
            color: Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.6),
            letterSpacing: 0.2,
            fontWeight: FontWeight.w500,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  // ═══════════════════════════════════════════════════════
  // LOGIN CARD
  // ═══════════════════════════════════════════════════════
  Widget _buildLoginCard(AuthState authState) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.25),
            blurRadius: 24,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Form(
        key: _formKey,
        autovalidateMode: _autovalidateMode,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Card header
            Text(
              'Welcome Back',
              style: PharmTextStyles.h2.copyWith(
                color: Theme.of(context).textTheme.displaySmall?.color,
                fontSize: 24,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Login to continue your VR pharmaceutical training.',
              style: PharmTextStyles.bodyMedium.copyWith(
                color: Theme.of(context).textTheme.bodySmall?.color?.withOpacity(0.6),
              ),
            ),
            const SizedBox(height: 32),

            const SizedBox(height: 28),

            // ── Fields ──
            _buildEmailField(),
            const SizedBox(height: 20),
            _buildPasswordField(),

            // Forgot password
            const SizedBox(height: 12),
            Align(
              alignment: Alignment.centerRight,
              child: GestureDetector(
                onTap: () => context.push('/auth/forgot-password'),
                child: Text(
                  'Forgot Password?',
                  style: PharmTextStyles.label.copyWith(
                    color: PharmColors.primary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // ── Remember Me ──
            Row(
              children: [
                SizedBox(
                  width: 24,
                  height: 24,
                  child: Checkbox(
                    value: _rememberMe,
                    onChanged: (v) => setState(() => _rememberMe = v ?? false),
                    activeColor: PharmColors.primary,
                    checkColor: Theme.of(context).scaffoldBackgroundColor,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                    side: BorderSide(color: Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.5) ?? PharmColors.textTertiary.withOpacity(0.5)),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: () => setState(() => _rememberMe = !_rememberMe),
                  child: Text(
                    'Remember Me',
                    style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // ── Primary CTA ──
            _buildLoginButton(authState),
            const SizedBox(height: 24),

            // ── Secondary / Dev CTA ──
            _buildQuickLoginButton(authState),
          ],
        ),
      ),
    );
  }

  // ── Label ──
  Widget _buildInputLabel(String text) {
    return Text(
      text,
      style: PharmTextStyles.overline.copyWith(
        color: Theme.of(context).textTheme.labelSmall?.color,
        letterSpacing: 1.6,
      ),
    );
  }

  // ── Email Field ──
  Widget _buildEmailField() {
    return PharmTextField(
      controller: _emailController,
      focusNode: _emailFocus,
      labelText: 'EMAIL',
      hintText: 'name@university.ac.id',
      prefixIcon: const Icon(Icons.email_outlined),
      keyboardType: TextInputType.emailAddress,
      textInputAction: TextInputAction.next,
      autofillHints: const [AutofillHints.email],
      onFieldSubmitted: (_) => _passwordFocus.requestFocus(),
      validator: ValidatorBuilder.validateEmail,
    );
  }

  Widget _buildPasswordField() {
    return PharmTextField(
      controller: _passwordController,
      focusNode: _passwordFocus,
      labelText: 'PASSWORD',
      hintText: '••••••••',
      prefixIcon: const Icon(Icons.lock_outline),
      isPasswordField: true,
      textInputAction: TextInputAction.done,
      autofillHints: const [AutofillHints.password],
      onFieldSubmitted: (_) => _handleLogin(),
      validator: ValidatorBuilder.validatePassword,
    );
  }


  // ── Primary Login Button ──
  Widget _buildLoginButton(AuthState authState) {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: DecoratedBox(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          gradient: authState.isLoading
              ? null
              : const LinearGradient(
                  colors: [PharmColors.primary, PharmColors.primaryDark],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
          color: authState.isLoading ? PharmColors.primaryDark.withOpacity(0.4) : null,
          boxShadow: authState.isLoading
              ? []
              : [
                  BoxShadow(
                    color: PharmColors.primary.withOpacity(0.25),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  ),
                ],
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            borderRadius: BorderRadius.circular(14),
            onTap: authState.isLoading ? null : _handleLogin,
            child: Center(
              child: authState.isLoading
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        color: Colors.white,
                        strokeWidth: 2.5,
                      ),
                    )
                  : Text(
                      'LOGIN',
                      style: PharmTextStyles.button.copyWith(
                        color: Colors.white,
                        letterSpacing: 1.4,
                      ),
                    ),
            ),
          ),
        ),
      ),
    );
  }

  // ── Quick Login (Testing) ──
  Widget _buildQuickLoginButton(AuthState authState) {
    return Center(
      child: TextButton(
        onPressed: authState.isLoading
            ? null
            : () {
                _emailController.text = 'test@pharmvr.com';
                _passwordController.text = 'Password123!';
                _handleLogin();
              },
        style: TextButton.styleFrom(
          foregroundColor: PharmColors.primary.withOpacity(0.5),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        ),
        child: Text(
          'Quick Login (Dev Protocol)',
          style: PharmTextStyles.overline.copyWith(
            color: authState.isLoading 
                ? Theme.of(context).textTheme.labelSmall?.color 
                : PharmColors.primary.withOpacity(0.5),
            fontSize: 10,
            letterSpacing: 1.2,
          ),
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════════════════
  // BOTTOM HELPER
  // ═══════════════════════════════════════════════════════
  Widget _buildBottomHelper() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          "Don't have an account?",
          style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.labelSmall?.color),
        ),
        TextButton(
          onPressed: () {
            _resetForm();
            context.go('/auth/register');
          },
          style: TextButton.styleFrom(
            padding: const EdgeInsets.only(left: 4),
            minimumSize: Size.zero,
            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          ),
          child: Text(
            'Sign up',
            style: PharmTextStyles.bodyBold.copyWith(color: PharmColors.primary),
          ),
        ),
      ],
    );
  }
}
