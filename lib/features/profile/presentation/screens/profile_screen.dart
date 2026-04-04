import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/widgets/pharm_primary_button.dart';
import '../providers/profile_provider.dart';
import '../../../auth/presentation/providers/auth_provider.dart';
import '../../../../core/theme/theme_provider.dart';
import '../../../../core/config/network_constants.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileState = ref.watch(profileProvider);
    final themeMode = ref.watch(themeProvider);
    final user = profileState.user;
    
    final l10n = AppLocalizations.of(context)!;
    final themeLabel = themeMode == ThemeMode.light 
        ? l10n.themeLight 
        : themeMode == ThemeMode.dark 
            ? l10n.themeDark 
            : l10n.themeSystem;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header
            _ProfileHeader(
              name: user?.name ?? 'PharmVR User',
              email: user?.email ?? 'user@pharmvr.com',
              avatarUrl: user?.profile?.avatarUrl,
              role: user?.role ?? 'VR Learner',
              university: user?.profile?.university,
              nim: user?.profile?.nim,
            ),

            // Sections
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Account
                  _SectionGroup(title: l10n.account, items: [
                    _MenuItem(icon: Icons.person_outline, label: l10n.editProfile,
                      onTap: () => context.push('/profile/edit')),
                    _MenuItem(icon: Icons.lock_outline, label: l10n.changePassword, 
                      onTap: () => context.push('/profile/change-password')),
                  ]),
                  const SizedBox(height: 20),

                  // Preferences
                  _SectionGroup(title: l10n.preferences, items: [
                    _MenuItem(icon: Icons.notifications_none, label: l10n.notifications, trailing: 'On', 
                      onTap: () => context.push('/settings/notifications')),
                    _MenuItem(icon: Icons.language, label: l10n.language, trailing: Localizations.localeOf(context).languageCode == 'id' ? l10n.indonesian : l10n.english, 
                      onTap: () => context.push('/settings/language')),
                    _MenuItem(icon: Icons.palette_outlined, label: l10n.appearance, trailing: themeLabel, 
                      onTap: () => context.push('/settings/appearance')),
                  ]),
                  const SizedBox(height: 20),

                  // Support
                  _SectionGroup(title: l10n.support, items: [
                    _MenuItem(icon: Icons.help_outline, label: l10n.helpCenter, 
                      onTap: () => context.push('/support/help-center')),
                    _MenuItem(icon: Icons.info_outline, label: l10n.aboutPharmVr, 
                      onTap: () => context.push('/support/about')),
                  ]),
                  const SizedBox(height: 20),

                  // Logout
                  _LogoutButton(onTap: () => _showLogout(context, ref)),
                  const SizedBox(height: 24),

                  // Version
                  Center(
                    child: Text('PharmVR v1.0.0', style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
                  ),
                  const SizedBox(height: 100),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showLogout(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Theme.of(context).colorScheme.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 56, height: 56,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: PharmColors.error.withValues(alpha: 0.1),
                ),
                child: const Icon(Icons.logout, color: PharmColors.error, size: 26),
              ),
              const SizedBox(height: 16),
              Text(l10n.logOutQuestion, style: PharmTextStyles.h3.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
              const SizedBox(height: 8),
              Text(
                l10n.logOutDescription,
                style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, height: 1.5),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: SizedBox(
                      height: 46,
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(ctx),
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(color: Theme.of(context).dividerColor),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: Text(l10n.cancel, style: PharmTextStyles.label.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, fontWeight: FontWeight.w600)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: SizedBox(
                      height: 46,
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          color: PharmColors.error,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Material(
                          color: Colors.transparent,
                          child: InkWell(
                            borderRadius: BorderRadius.circular(12),
                            onTap: () {
                              Navigator.pop(ctx);
                              ref.read(authProvider.notifier).logout();
                              context.go('/auth/login');
                            },
                            child: Center(
                              child: Text(l10n.logout.toUpperCase(), style: PharmTextStyles.label.copyWith(color: Colors.white, fontWeight: FontWeight.w700, letterSpacing: 0.8)),
                            ),
                          ),
                        ),
                      ),
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

// ═══════════════════════════════════════════════════════════
// PROFILE HEADER
// ═══════════════════════════════════════════════════════════
class _ProfileHeader extends StatelessWidget {
  final String name;
  final String email;
  final String role;
  final String? avatarUrl;
  final String? university;
  final String? nim;
  
  const _ProfileHeader({
    required this.name, 
    required this.email, 
    required this.role, 
    this.avatarUrl,
    this.university,
    this.nim,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.only(top: MediaQuery.of(context).padding.top + 16, bottom: 28),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            Theme.of(context).primaryColor.withValues(alpha: 0.12),
            Theme.of(context).scaffoldBackgroundColor,
          ],
        ),
      ),
      child: Column(
        children: [
          // Avatar
          Hero(
            tag: 'profile_avatar',
            child: Container(
              width: 88, height: 88,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: Theme.of(context).primaryColor.withValues(alpha: 0.4), width: 2.5),
                boxShadow: [BoxShadow(color: Theme.of(context).primaryColor.withValues(alpha: 0.15), blurRadius: 24, spreadRadius: 4)],
              ),
              child: CircleAvatar(
                backgroundColor: Theme.of(context).colorScheme.surface,
                backgroundImage: avatarUrl != null ? CachedNetworkImageProvider(NetworkConstants.sanitizeUrl(avatarUrl!)) : null,
                child: avatarUrl == null
                    ? Text(name.isNotEmpty ? name[0].toUpperCase() : 'U',
                        style: PharmTextStyles.h1.copyWith(color: Theme.of(context).primaryColor, fontSize: 34))
                    : null,
              ),
            ),
          ),
          const SizedBox(height: 14),
          Text(name, style: PharmTextStyles.h3.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
          const SizedBox(height: 4),
          Text(email, style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color)),
          if (university != null || nim != null) ...[
            const SizedBox(height: 8),
            Text(
              '${university ?? ""}${university != null && nim != null ? " • " : ""}${nim ?? ""}',
              style: PharmTextStyles.caption.copyWith(color: Theme.of(context).primaryColor.withValues(alpha: 0.8), fontWeight: FontWeight.w500),
            ),
          ],
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: Theme.of(context).primaryColor.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              role.toUpperCase(),
              style: PharmTextStyles.caption.copyWith(
                color: Theme.of(context).primaryColor,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.2,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════
// SECTION GROUP
// ═══════════════════════════════════════════════════════════
class _SectionGroup extends StatelessWidget {
  final String title;
  final List<_MenuItem> items;
  const _SectionGroup({required this.title, required this.items});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 10),
          child: Text(title, style: PharmTextStyles.overline.copyWith(color: Theme.of(context).textTheme.labelSmall?.color, letterSpacing: 2)),
        ),
        Container(
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : Theme.of(context).dividerColor.withOpacity(0.5)),
          ),
          child: Column(
            children: List.generate(items.length, (i) {
              final item = items[i];
              final isLast = i == items.length - 1;
              return Column(
                children: [
                  _MenuRow(icon: item.icon, label: item.label, trailing: item.trailing, onTap: item.onTap),
                  if (!isLast) Divider(height: 1, indent: 56, color: Theme.of(context).dividerColor.withOpacity(0.5)),
                ],
              );
            }),
          ),
        ),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════
// MENU ITEM DATA + ROW
// ═══════════════════════════════════════════════════════════
class _MenuItem {
  final IconData icon;
  final String label;
  final String? trailing;
  final VoidCallback onTap;
  const _MenuItem({required this.icon, required this.label, required this.onTap, this.trailing});
}

class _MenuRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String? trailing;
  final VoidCallback onTap;
  const _MenuRow({required this.icon, required this.label, required this.onTap, this.trailing});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          child: Row(
            children: [
              Container(
                width: 36, height: 36,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(10),
                  color: Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight,
                ),
                child: Icon(icon, color: Theme.of(context).textTheme.bodySmall?.color, size: 18),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(label, style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
              ),
              if (trailing != null) ...[
                Text(trailing!, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
                const SizedBox(width: 8),
              ],
              Icon(Icons.arrow_forward_ios, size: 12, color: Theme.of(context).primaryColor.withOpacity(0.5)),
            ],
          ),
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════
// LOGOUT BUTTON
// ═══════════════════════════════════════════════════════════
class _LogoutButton extends StatelessWidget {
  final VoidCallback onTap;
  const _LogoutButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: PharmColors.error.withValues(alpha: 0.06),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: PharmColors.error.withValues(alpha: 0.12)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.logout, color: PharmColors.error, size: 18),
              const SizedBox(width: 10),
              Text(l10n.logout, style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.error, fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ),
    );
  }
}
