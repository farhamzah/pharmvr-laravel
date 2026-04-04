import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/utils/validators.dart';
import '../../../../core/utils/error_handler.dart';
import '../providers/auth_provider.dart';
import '../../../../core/widgets/pharm_responsive_wrapper.dart';
import '../../../../core/widgets/pharm_text_field.dart';

class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  bool _isSent = false;
  AutovalidateMode _autovalidateMode = AutovalidateMode.disabled;

  late AnimationController _fadeController;
  late Animation<double> _fadeAnim;
  late Animation<Offset> _slideAnim;

  @override
  void initState() {
    super.initState();
    _fadeController = AnimationController(vsync: this, duration: const Duration(milliseconds: 900));
    _fadeAnim = CurvedAnimation(parent: _fadeController, curve: Curves.easeOut);
    _slideAnim = Tween<Offset>(begin: const Offset(0, 0.08), end: Offset.zero)
        .animate(CurvedAnimation(parent: _fadeController, curve: Curves.easeOutCubic));
    _fadeController.forward();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _fadeController.dispose();
    super.dispose();
  }

  void _handleReset() async {
    FocusScope.of(context).unfocus();
    setState(() => _autovalidateMode = AutovalidateMode.onUserInteraction);
    if (_formKey.currentState?.validate() ?? false) {
      ref.read(authProvider.notifier).clearError();
      try {
        await ref.read(authProvider.notifier).resetPassword(_emailController.text.trim());
        if (mounted) setState(() => _isSent = true);
      } catch (e) {
        if (mounted) {
          PharmErrorHandler.showError(context, PharmErrorHandler.sanitizeError(e));
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    return PharmResponsiveWrapper(
      child: Scaffold(
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        body: Stack(
          children: [
            // Subtle background glow
            Positioned(
              top: -60, right: -40,
              child: Container(
                width: 200, height: 200,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(colors: [PharmColors.primary.withOpacity(0.06), Colors.transparent]),
                ),
              ),
            ),

            SafeArea(
              child: Column(
                children: [
                  // App bar
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    child: Row(
                      children: [
                        IconButton(
                          icon: Icon(Icons.arrow_back_ios_new, color: Theme.of(context).textTheme.labelSmall?.color, size: 20),
                          onPressed: () => context.pop(),
                        ),
                        const Spacer(),
                      ],
                    ),
                  ),

                  // Main content
                  Expanded(
                    child: Center(
                      child: SingleChildScrollView(
                        padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
                        child: FadeTransition(
                          opacity: _fadeAnim,
                          child: SlideTransition(
                            position: _slideAnim,
                            child: _isSent ? _buildSuccessState() : _buildFormState(authState),
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ===============================================
  // FORM STATE
  // ===============================================
  Widget _buildFormState(AuthState authState) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        // Icon with subtle glow ring (Aligned with Login logo size concept)
        Container(
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
          child: Container(
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Theme.of(context).colorScheme.surface,
              border: Border.all(
                color: PharmColors.primary.withOpacity(0.2),
              ),
            ),
            child: const Icon(Icons.lock_reset_rounded, size: 42, color: PharmColors.primary),
          ),
        ),
        const SizedBox(height: 12),

        Text(
          'Reset Access',
          style: PharmTextStyles.h1.copyWith(
            color: PharmColors.primary,
            fontSize: 30,
          ),
        ),
        const SizedBox(height: 4),

        Text(
          'Reset your VR training credentials',
          style: PharmTextStyles.bodyMedium.copyWith(
            color: Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.6),
            letterSpacing: 0.2,
            fontWeight: FontWeight.w500,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 32),

        // Card
        Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : Theme.of(context).dividerColor.withOpacity(0.5)),
            boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 20, offset: const Offset(0, 6))],
          ),
          child: Form(
            key: _formKey,
            autovalidateMode: _autovalidateMode,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                PharmTextField(
                  controller: _emailController,
                  labelText: 'EMAIL',
                  hintText: 'name@university.ac.id',
                  prefixIcon: const Icon(Icons.email_outlined),
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.done,
                  onFieldSubmitted: (_) => _handleReset(),
                  validator: ValidatorBuilder.validateEmail,
                ),
                const SizedBox(height: 24),

                // CTA
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
                        onTap: authState.isLoading ? null : _handleReset,
                        child: Center(
                          child: authState.isLoading
                              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                              : Text('SEND RESET LINK', style: PharmTextStyles.button.copyWith(color: Colors.white, letterSpacing: 1.4)),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),

        // Back to login
        TextButton(
          onPressed: () => context.pop(),
          child: Text('Back to Login', style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.primary)),
        ),
      ],
    );
  }

  // ===============================================
  // SUCCESS STATE
  // ===============================================
  Widget _buildSuccessState() {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        // Success icon
        Container(
          width: 88,
          height: 88,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: PharmColors.success.withOpacity(0.1),
                blurRadius: 40,
                spreadRadius: 2,
              ),
            ],
          ),
          child: Container(
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Theme.of(context).colorScheme.surface,
              border: Border.all(
                color: PharmColors.success.withOpacity(0.2),
              ),
            ),
            child: const Icon(Icons.mark_email_read_rounded, size: 42, color: PharmColors.success),
          ),
        ),
        const SizedBox(height: 28),

        Text(
          'Check Your Email',
          style: PharmTextStyles.h1.copyWith(
            color: PharmColors.success,
            fontSize: 30,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          'If an account exists with that email, you will receive\npassword reset instructions shortly.',
          style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, height: 1.6),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            color: Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(
            _emailController.text.trim(),
            style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.primary, fontWeight: FontWeight.w600),
          ),
        ),
        const SizedBox(height: 36),

        // Tip
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
          ),
          child: Row(
            children: [
              Container(
                width: 36, height: 36,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(10),
                  color: PharmColors.info.withOpacity(0.1),
                ),
                child: const Icon(Icons.info_outline, size: 18, color: PharmColors.info),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Did not receive it? Check your spam folder or try again in a few minutes.',
                      style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color, height: 1.4),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '(Dev: Check backend/storage/logs/laravel.log for the link)',
                      style: PharmTextStyles.caption.copyWith(color: PharmColors.info, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 28),

        // Resend + Login buttons
        Row(
          children: [
            Expanded(
              child: SizedBox(
                height: 48,
                child: OutlinedButton(
                  onPressed: () => setState(() => _isSent = false),
                  style: OutlinedButton.styleFrom(
                    side: BorderSide(color: PharmColors.primary.withOpacity(0.3)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: Text('Try Again', style: PharmTextStyles.label.copyWith(color: PharmColors.primary, fontWeight: FontWeight.w600)),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: SizedBox(
                height: 48,
                child: DecoratedBox(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(14),
                    gradient: const LinearGradient(
                      colors: [PharmColors.primary, PharmColors.primaryDark],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: PharmColors.primary.withOpacity(0.2),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(14),
                      onTap: () => context.go('/auth/login'),
                      child: Center(
                        child: Text('BACK TO LOGIN', style: PharmTextStyles.label.copyWith(color: Colors.white, fontWeight: FontWeight.w700, letterSpacing: 0.8)),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

