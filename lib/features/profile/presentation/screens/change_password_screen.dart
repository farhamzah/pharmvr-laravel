import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/utils/validators.dart';
import '../../../../core/widgets/pharm_primary_button.dart';
import '../../../../core/widgets/pharm_text_field.dart';
import '../providers/profile_provider.dart';

/// A premium, secure screen for updating the user's password.
/// It uses a calm dark background, clean cards, and strong local validation
/// before sending the request to the backend.
class ChangePasswordScreen extends ConsumerStatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  ConsumerState<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends ConsumerState<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isLoading = false;
  String? _errorMessage;
  bool _isSuccess = false;

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _errorMessage = null;
    });

    try {
      await ref.read(profileProvider.notifier).changePassword(
            currentPassword: _currentPasswordController.text,
            newPassword: _newPasswordController.text,
            newPasswordConfirmation: _confirmPasswordController.text,
          );

      if (mounted) {
        setState(() {
          _isSuccess = true;
        });

        // Automatically navigate back after showing success message briefly
        Future.delayed(const Duration(seconds: 2), () {
          if (mounted) context.pop();
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString().contains('current_password')
              ? 'Current password incorrect.'
              : 'Failed to update password. Please try again.';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text('Change Password', style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
        backgroundColor: PharmColors.surface,
        elevation: 0,
        centerTitle: true,
        leading: ref.watch(profileProvider).isLoading ? null : IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: PharmColors.primary),
          onPressed: () => context.pop(),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: PharmSpacing.allLg,
          child: _isSuccess ? _buildSuccessState() : _buildForm(ref.watch(profileProvider).isLoading),
        ),
      ),
    );
  }

  Widget _buildForm(bool isLoading) {
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Instructional Text
          Text(
            'Ensure your account is using a long, random password to stay secure.',
            style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.5),
          ),
          const SizedBox(height: 32),

          // Error Banner
          if (_errorMessage != null) ...[
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: PharmColors.error.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: PharmColors.error.withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.error_outline, color: PharmColors.error, size: 20),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      _errorMessage!,
                      style: PharmTextStyles.caption.copyWith(color: PharmColors.error),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
          ],

          // Form Card
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: PharmColors.surface,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: PharmColors.cardBorder),
              boxShadow: [
                BoxShadow(color: Colors.black.withValues(alpha: 0.2), blurRadius: 20, offset: const Offset(0, 8)),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                PharmTextField(
                  labelText: 'Current Password',
                  hintText: 'Enter your current password',
                  controller: _currentPasswordController,
                  isPasswordField: true,
                  prefixIcon: const Icon(Icons.lock_outline),
                  validator: (v) => ValidatorBuilder.validateRequired(v, 'Current password'),
                  textInputAction: TextInputAction.next,
                ),
                const SizedBox(height: 24),
                Divider(color: PharmColors.divider.withValues(alpha: 0.5), height: 1),
                const SizedBox(height: 24),
                PharmTextField(
                  labelText: 'New Password',
                  hintText: 'Min. 8 characters, 1 uppercase, 1 number',
                  controller: _newPasswordController,
                  isPasswordField: true,
                  prefixIcon: const Icon(Icons.lock_reset),
                  validator: ValidatorBuilder.validatePassword,
                  textInputAction: TextInputAction.next,
                ),
                const SizedBox(height: 20),
                PharmTextField(
                  labelText: 'Confirm New Password',
                  hintText: 'Re-enter your new password',
                  controller: _confirmPasswordController,
                  isPasswordField: true,
                  prefixIcon: const Icon(Icons.verified_user_outlined),
                  validator: (v) => ValidatorBuilder.validateConfirmPassword(_newPasswordController.text)(v),
                  textInputAction: TextInputAction.done,
                  onFieldSubmitted: (_) => _submit(), // Allow enter to submit
                ),
              ],
            ),
          ),
          const SizedBox(height: 32),

          // Submit Action
          PharmPrimaryButton(
            text: 'Update Password',
            icon: Icons.check_circle_outline,
            isLoading: isLoading,
            onPressed: _submit,
          ),
        ],
      ),
    );
  }

  Widget _buildSuccessState() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 48, horizontal: 24),
      decoration: BoxDecoration(
        color: PharmColors.surface,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: PharmColors.success.withValues(alpha: 0.3)),
        boxShadow: [
          BoxShadow(
            color: PharmColors.success.withValues(alpha: 0.1),
            blurRadius: 40,
            spreadRadius: 10,
          )
        ],
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: PharmColors.success.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.check_circle, size: 64, color: PharmColors.success),
          ),
          const SizedBox(height: 24),
          Text(
            'Password Updated',
            style: PharmTextStyles.h3.copyWith(color: PharmColors.textPrimary),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 12),
          Text(
            'Your password has been changed successfully. Navigating back...',
            style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.5),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
