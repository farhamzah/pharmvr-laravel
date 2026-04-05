import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/utils/validators.dart';
import '../../../../core/utils/error_handler.dart';
import '../../../../core/widgets/password_strength_indicator.dart';
import '../providers/auth_provider.dart';
import '../../../../core/widgets/pharm_responsive_wrapper.dart';
import '../../../../core/widgets/pharm_text_field.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();
  final _nameFocus = FocusNode();
  final _emailFocus = FocusNode();
  final _passwordFocus = FocusNode();
  final _confirmFocus = FocusNode();

  AutovalidateMode _autovalidateMode = AutovalidateMode.disabled;

  @override
  void initState() {
    super.initState();
    
    // Clear any previous state on entry with a slight delay
    // to override any OS-level Autofill values
    Future.delayed(const Duration(milliseconds: 100), () {
      if (mounted) _resetForm();
    });
  }

  void _resetForm() {
    if (!mounted) return;
    FocusScope.of(context).unfocus();
    _nameController.text = '';
    _emailController.text = '';
    _passwordController.text = '';
    _confirmController.text = '';
    _formKey.currentState?.reset();
    setState(() {
      _autovalidateMode = AutovalidateMode.disabled;
      _autovalidateMode = AutovalidateMode.disabled;
    });
    ref.read(authProvider.notifier).resetState();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    _nameFocus.dispose();
    _emailFocus.dispose();
    _passwordFocus.dispose();
    _confirmFocus.dispose();
    super.dispose();
  }

  void _handleRegister() async {
    FocusScope.of(context).unfocus();
    setState(() => _autovalidateMode = AutovalidateMode.onUserInteraction);
    if (_formKey.currentState?.validate() ?? false) {
      ref.read(authProvider.notifier).clearError();
      await ref.read(authProvider.notifier).register(
        _nameController.text.trim(),
        _emailController.text.trim(),
        _passwordController.text,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<AuthState>(authProvider, (previous, next) {
      if (next.error != null && (previous?.error != next.error)) {
        PharmErrorHandler.showError(context, PharmErrorHandler.sanitizeError(next.error!));
      } else if (next.registrationSuccess && !(previous?.registrationSuccess ?? false)) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Akun berhasil dibuat! Silakan masuk.'),
            backgroundColor: PharmColors.success,
            behavior: SnackBarBehavior.floating,
          ),
        );
        _resetForm(); // Clear everything before leaving
        context.go('/auth/login');
      }
    });

    final authState = ref.watch(authProvider);

    return PharmResponsiveWrapper(
      child: Scaffold(
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _buildBranding(),
                  const SizedBox(height: 32),
                  _buildRegisterCard(authState),
                  const SizedBox(height: 28),
                  _buildBottomHelper(),
                  const SizedBox(height: 16),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════════════════
  // BRANDING
  // ═══════════════════════════════════════════════════════
  Widget _buildBranding() {
    return Column(
      children: [
        Container(
          width: 88,
          height: 88,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            boxShadow: [BoxShadow(color: PharmColors.primary.withOpacity(0.1), blurRadius: 40, spreadRadius: 2)],
          ),
          child: ClipOval(
            child: Image.asset(
              'assets/images/Pharmvrlogo.png',
              fit: BoxFit.contain,
              errorBuilder: (_, __, ___) => Container(
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Theme.of(context).colorScheme.surface,
                  border: Border.all(color: PharmColors.primary.withOpacity(0.3)),
                ),
                child: const Icon(Icons.vaccines, size: 42, color: PharmColors.primary),
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
          'Start your immersive CPOB learning journey',
          style: PharmTextStyles.bodyMedium.copyWith(
            color: Theme.of(context).brightness == Brightness.dark
                ? Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.6)
                : Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.85),
            letterSpacing: 0.2,
            fontWeight: FontWeight.w500,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  // ═══════════════════════════════════════════════════════
  // REGISTER CARD
  // ═══════════════════════════════════════════════════════
  Widget _buildRegisterCard(AuthState authState) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.25), blurRadius: 24, offset: const Offset(0, 8))],
      ),
      child: Form(
        key: _formKey,
        autovalidateMode: _autovalidateMode,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Create Account',
              style: PharmTextStyles.h2.copyWith(
                color: Theme.of(context).textTheme.displaySmall?.color,
                fontSize: 24,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Join the future of VR pharmaceutical training.',
              style: PharmTextStyles.bodyMedium.copyWith(
                color: Theme.of(context).textTheme.bodySmall?.color?.withOpacity(0.6),
              ),
            ),
            const SizedBox(height: 32),

            // ── Fields ──
            PharmTextField(
              controller: _nameController,
              labelText: 'FULL NAME',
              hintText: 'John Doe',
              prefixIcon: const Icon(Icons.person_outline),
              textInputAction: TextInputAction.next,
              onFieldSubmitted: (_) => _emailFocus.requestFocus(),
              validator: ValidatorBuilder.validateName,
            ),
            const SizedBox(height: 18),
            
            PharmTextField(
              controller: _emailController,
              labelText: 'EMAIL',
              hintText: 'name@unpad.ac.id',
              prefixIcon: const Icon(Icons.email_outlined),
              keyboardType: TextInputType.emailAddress,
              textInputAction: TextInputAction.next,
              onFieldSubmitted: (_) => _passwordFocus.requestFocus(),
              validator: ValidatorBuilder.validateEmail,
            ),
            const SizedBox(height: 18),

            PharmTextField(
              controller: _passwordController,
              labelText: 'PASSWORD',
              hintText: 'Min 8 characters',
              prefixIcon: const Icon(Icons.lock_outline),
              isPasswordField: true,
              textInputAction: TextInputAction.next,
              onFieldSubmitted: (_) => _confirmFocus.requestFocus(),
              validator: ValidatorBuilder.validatePassword,
            ),
            ValueListenableBuilder(
              valueListenable: _passwordController,
              builder: (context, value, _) {
                return PasswordStrengthIndicator(password: value.text);
              },
            ),
            const SizedBox(height: 18),

            PharmTextField(
              controller: _confirmController,
              focusNode: _confirmFocus,
              labelText: 'CONFIRM PASSWORD',
              hintText: 'Re-enter password',
              prefixIcon: const Icon(Icons.lock_outline),
              isPasswordField: true,
              textInputAction: TextInputAction.done,
              onFieldSubmitted: (_) => _handleRegister(),
              validator: (value) => ValidatorBuilder.validateConfirmPassword(_passwordController.text)(value),
            ),
            const SizedBox(height: 28),

            // ── Primary CTA ──
            SizedBox(
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
                      : [BoxShadow(color: PharmColors.primary.withOpacity(0.25), blurRadius: 16, offset: const Offset(0, 4))],
                ),
                child: Material(
                  color: Colors.transparent,
                  child: InkWell(
                    borderRadius: BorderRadius.circular(14),
                    onTap: authState.isLoading ? null : _handleRegister,
                    child: Center(
                      child: authState.isLoading
                          ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                          : Text('CREATE ACCOUNT', style: PharmTextStyles.button.copyWith(color: Colors.white, letterSpacing: 1.4)),
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
  // BOTTOM HELPER
  // ═══════════════════════════════════════════════════════
  Widget _buildBottomHelper() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text('Already have an account?', style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
        TextButton(
          onPressed: () {
            _resetForm();
            context.go('/auth/login');
          },
          style: TextButton.styleFrom(padding: const EdgeInsets.only(left: 4), minimumSize: Size.zero, tapTargetSize: MaterialTapTargetSize.shrinkWrap),
          child: Text('Login', style: PharmTextStyles.bodyBold.copyWith(color: PharmColors.primary)),
        ),
      ],
    );
  }
}
