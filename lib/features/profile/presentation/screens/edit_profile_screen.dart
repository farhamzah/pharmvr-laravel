import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import 'dart:ui' as ui;
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/utils/validators.dart';
import '../../../../core/utils/error_handler.dart';
import '../../../../core/widgets/pharm_primary_button.dart';
import '../../../../core/widgets/pharm_text_field.dart';
import '../../domain/models/edit_profile_request.dart';
import '../providers/edit_profile_provider.dart';
import '../providers/profile_provider.dart';
import '../../../../core/config/network_constants.dart';

class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key});

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _phoneCtrl;
  late final TextEditingController _uniCtrl;
  late final TextEditingController _semCtrl;
  late final TextEditingController _nimCtrl;
  AutovalidateMode _autovalidateMode = AutovalidateMode.disabled;
  String? _selectedImagePath;

  @override
  void initState() {
    super.initState();
    final user = ref.read(profileProvider).user;
    _nameCtrl = TextEditingController(text: user?.name ?? '');
    _emailCtrl = TextEditingController(text: user?.email ?? '');
    _phoneCtrl = TextEditingController(text: user?.phone ?? '');
    _uniCtrl = TextEditingController(text: user?.university ?? '');
    _semCtrl = TextEditingController(text: user?.semester?.toString() ?? '');
    _nimCtrl = TextEditingController(text: user?.nim ?? '');
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _uniCtrl.dispose();
    _semCtrl.dispose();
    _nimCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => Container(
        padding: EdgeInsets.only(
          top: 24,
          left: 20,
          right: 20,
          bottom: MediaQuery.of(context).padding.bottom + 24,
        ),
        decoration: BoxDecoration(
          color: PharmColors.surface,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.5),
              blurRadius: 40,
              offset: const Offset(0, -10),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40, height: 4,
              margin: const EdgeInsets.only(bottom: 24),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.2),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Text(
              'Profile Photo', 
              style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary),
            ),
            const SizedBox(height: 28),
            _ImageSourceTile(
              icon: Icons.camera_alt_rounded,
              label: 'Camera',
              subtitle: 'Take a photo directly from your camera',
              onTap: () {
                Navigator.pop(context);
                _handleImagePick(ImageSource.camera);
              },
            ),
            const SizedBox(height: 12),
            _ImageSourceTile(
              icon: Icons.photo_library_rounded,
              label: 'Gallery',
              subtitle: 'Choose an image from your device storage',
              onTap: () {
                Navigator.pop(context);
                _handleImagePick(ImageSource.gallery);
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _handleImagePick(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final XFile? image = await picker.pickImage(
        source: source,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 85,
      );
      
      if (image != null) {
        if (mounted) {
          setState(() {
            _selectedImagePath = image.path;
          });
        }
      }
    } catch (e) {
      debugPrint('Error picking image: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not access ${source == ImageSource.camera ? 'camera' : 'gallery'}. Please check permissions.')),
        );
      }
    }
  }

  void _submit() {
    FocusScope.of(context).unfocus();
    setState(() => _autovalidateMode = AutovalidateMode.onUserInteraction);
    if (_formKey.currentState?.validate() ?? false) {
      ref.read(editProfileProvider.notifier).submitProfileChange(
        EditProfileRequest(
          fullName: _nameCtrl.text.trim(),
          email: _emailCtrl.text.trim(),
          phoneNumber: _phoneCtrl.text.trim(),
          university: _uniCtrl.text.trim(),
          semester: _semCtrl.text.trim(),
          nim: _nimCtrl.text.trim(),
          imagePath: _selectedImagePath,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    // Watch profileProvider to update controllers if data arrives asynchronously
    ref.listen(profileProvider.select((s) => s.user), (previous, next) {
      if (next != null) {
        if (_nameCtrl.text.isEmpty) _nameCtrl.text = next.name;
        if (_emailCtrl.text.isEmpty) _emailCtrl.text = next.email;
        if (_phoneCtrl.text.isEmpty) _phoneCtrl.text = next.phone ?? '';
        if (_uniCtrl.text.isEmpty) _uniCtrl.text = next.university ?? '';
        if (_semCtrl.text.isEmpty) _semCtrl.text = next.semester?.toString() ?? '';
        if (_nimCtrl.text.isEmpty) _nimCtrl.text = next.nim ?? '';
      }
    });

    final editState = ref.watch(editProfileProvider);

    ref.listen<EditProfileState>(editProfileProvider, (prev, next) {
      if (next.error != null && prev?.error != next.error) {
        PharmErrorHandler.showError(context, PharmErrorHandler.sanitizeError(next.error!));
      } else if (next.isSuccess) {
        PharmErrorHandler.showSuccess(context, 'Profile updated successfully');
        context.pop();
      }
    });

    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        backgroundColor: PharmColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: PharmColors.textSecondary, size: 20),
          onPressed: () => context.pop(),
        ),
        title: Text('Edit Profile', style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary)),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(height: 1, color: PharmColors.divider),
        ),
      ),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                child: Form(
                  key: _formKey,
                  autovalidateMode: _autovalidateMode,
                  child: Column(
                    children: [
                      // Avatar
                      _buildAvatar(),
                      const SizedBox(height: 28),
        
                      // Personal Information
                      _FormSection(
                        title: 'Personal Information',
                        children: [
                          PharmTextField(
                            labelText: 'FULL NAME',
                            controller: _nameCtrl,
                            prefixIcon: const Icon(Icons.person_outline),
                            textInputAction: TextInputAction.next,
                            validator: ValidatorBuilder.validateName,
                          ),
                          const SizedBox(height: 16),
                          PharmTextField(
                            labelText: 'EMAIL',
                            controller: _emailCtrl,
                            prefixIcon: const Icon(Icons.email_outlined),
                            keyboardType: TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                            validator: ValidatorBuilder.validateEmail,
                          ),
                          const SizedBox(height: 16),
                          PharmTextField(
                            labelText: 'PHONE NUMBER',
                            controller: _phoneCtrl,
                            prefixIcon: const Icon(Icons.phone_outlined),
                            keyboardType: TextInputType.phone,
                            textInputAction: TextInputAction.next,
                            validator: ValidatorBuilder.validatePhone,
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
        
                      // Academic Details
                      _FormSection(
                        title: 'Academic Details',
                        children: [
                          PharmTextField(
                            labelText: 'UNIVERSITY',
                            controller: _uniCtrl,
                            prefixIcon: const Icon(Icons.school_outlined),
                            textInputAction: TextInputAction.next,
                            validator: (v) => ValidatorBuilder.validateRequired(v, 'University'),
                          ),
                          const SizedBox(height: 16),
                          PharmTextField(
                            labelText: 'SEMESTER',
                            controller: _semCtrl,
                            prefixIcon: const Icon(Icons.calendar_today_outlined),
                            keyboardType: TextInputType.number,
                            textInputAction: TextInputAction.next,
                            validator: ValidatorBuilder.validateSemester,
                          ),
                          const SizedBox(height: 16),
                          PharmTextField(
                            labelText: 'NIM (STUDENT ID)',
                            controller: _nimCtrl,
                            prefixIcon: const Icon(Icons.badge_outlined),
                            textInputAction: TextInputAction.done,
                            validator: ValidatorBuilder.validateNim,
                            onFieldSubmitted: (_) => _submit(),
                          ),
                        ],
                      ),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
              ),
            ),
            
            // Fixed bottom container for button to ensure it's always accessible
            Container(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
              decoration: BoxDecoration(
                color: PharmColors.background,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.4),
                    blurRadius: 20,
                    offset: const Offset(0, -5),
                  ),
                ],
              ),
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: PharmColors.primary.withOpacity(0.25),
                      blurRadius: 15,
                      spreadRadius: -2,
                    ),
                  ],
                ),
                child: PharmPrimaryButton(
                  text: 'SAVE CHANGES',
                  icon: Icons.check_circle_outline,
                  isLoading: editState.isLoading,
                  onPressed: _submit,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Avatar ──
  Widget _buildAvatar() {
    final user = ref.watch(profileProvider).user;
    
    return Center(
      child: Stack(
        alignment: Alignment.bottomRight,
        children: [
          Hero(
            tag: 'profile_avatar',
            child: Container(
              width: 96, height: 96,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: PharmColors.primary.withOpacity(0.35), width: 2),
                boxShadow: [
                  BoxShadow(color: PharmColors.primary.withOpacity(0.2), blurRadius: 25, spreadRadius: 2),
                ],
              ),
              child: CircleAvatar(
                backgroundColor: PharmColors.surface,
                backgroundImage: _selectedImagePath != null 
                    ? FileImage(File(_selectedImagePath!))
                    : (user?.avatarUrl != null ? CachedNetworkImageProvider(NetworkConstants.sanitizeUrl(user!.avatarUrl!)) : null),
                child: (_selectedImagePath == null && user?.avatarUrl == null)
                    ? Text(
                        (user?.name ?? 'U').isNotEmpty ? (user?.name ?? 'U')[0].toUpperCase() : 'U',
                        style: PharmTextStyles.h1.copyWith(color: PharmColors.primary, fontSize: 34),
                      )
                    : null,
              ),
            ),
          ),
          Material(
            color: PharmColors.primary,
            shape: const CircleBorder(),
            child: InkWell(
              customBorder: const CircleBorder(),
              onTap: _pickImage,
              child: const Padding(
                padding: EdgeInsets.all(8),
                child: Icon(Icons.camera_alt, size: 16, color: PharmColors.background),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════
// FORM SECTION CARD
// ═══════════════════════════════════════════════════════════
class _FormSection extends StatelessWidget {
  final String title;
  final List<Widget> children;
  const _FormSection({required this.title, required this.children});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 8, bottom: 12),
          child: Text(
            title.toUpperCase(), 
            style: PharmTextStyles.overline.copyWith(
              color: PharmColors.primary,
              letterSpacing: 2.0,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        ClipRRect(
          borderRadius: BorderRadius.circular(24),
          child: Container(
            decoration: BoxDecoration(
              color: PharmColors.surface.withOpacity(0.4),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: Colors.white.withOpacity(0.08)),
            ),
            child: Stack(
              children: [
                // Glassmorphism blur effect
                Positioned.fill(
                  child: BackdropFilter(
                    filter: ui.ImageFilter.blur(sigmaX: 12, sigmaY: 12),
                    child: Container(color: Colors.transparent),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start, 
                    children: children,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _ImageSourceTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String subtitle;
  final VoidCallback onTap;

  const _ImageSourceTile({
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white.withOpacity(0.05),
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: PharmColors.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: PharmColors.primary, size: 24),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      label,
                      style: PharmTextStyles.bodyBold.copyWith(
                        color: PharmColors.textPrimary,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      subtitle,
                      style: PharmTextStyles.caption.copyWith(
                        color: PharmColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: PharmColors.textSecondary, size: 20),
            ],
          ),
        ),
      ),
    );
  }
}
