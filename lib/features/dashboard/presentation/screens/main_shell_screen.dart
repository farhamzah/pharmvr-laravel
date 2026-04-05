import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/widgets/pharm_responsive_wrapper.dart';
import '../../../ai_assistant/presentation/widgets/ai_quick_chat_modal.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';
import '../../../../features/auth/presentation/providers/auth_provider.dart';
import '../../../../core/models/user.dart';
import '../../../../core/models/user_profile.dart';
import '../providers/dashboard_provider.dart';
import 'package:flutter/foundation.dart' show kIsWeb;

class MainShellScreen extends ConsumerStatefulWidget {
  final StatefulNavigationShell navigationShell;

  const MainShellScreen({
    super.key,
    required this.navigationShell,
  });

  @override
  ConsumerState<MainShellScreen> createState() => _MainShellScreenState();
}

class _MainShellScreenState extends ConsumerState<MainShellScreen> {
  @override
  void initState() {
    super.initState();
    // On Web, the splash screen is often bypassed. 
    // We ensure the auth session is restored/checked here.
    if (kIsWeb) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _restoreSession();
      });
    }
  }

  Future<void> _restoreSession() async {
    final auth = ref.read(authProvider);
    if (!auth.isAuthenticated) {
      debugPrint('MainShellScreen: Restoring session for Web...');
      await ref.read(authProvider.notifier).checkAuth();
    }
  }

  void _onTap(BuildContext context, int index) {
    widget.navigationShell.goBranch(
      index,
      initialLocation: true,
    );
  }

  @override
  Widget build(BuildContext context) {
    // 1. Sync User State Listening
    // This is the most reliable way to sync global user state from the Dashboard data
    ref.listen(dashboardProvider, (previous, next) {
      next.whenData((data) {
        final greeting = data.userGreeting;
        if (greeting.isNotEmpty) {
          final auth = ref.read(authProvider.notifier);
          final currentUser = ref.read(authProvider).user;
          
          final updatedUser = User(
            id: currentUser?.id ?? 0,
            name: greeting['full_name'] as String? ?? currentUser?.name ?? 'User',
            email: greeting['email'] as String? ?? currentUser?.email ?? '',
            role: greeting['role'] as String? ?? currentUser?.role ?? 'Mahasiswa',
            profile: UserProfile(
              avatarUrl: greeting['avatar_url'] as String? ?? currentUser?.profile?.avatarUrl,
              university: greeting['institution'] as String? ?? currentUser?.profile?.university,
            ),
          );

          debugPrint('MainShellScreen Syncing User: ${updatedUser.name}');
          auth.updateUser(updatedUser);
        }
      });
    });

    final navigationShell = widget.navigationShell;
    final isAiTab = navigationShell.currentIndex == 2;
    final size = MediaQuery.of(context).size;
    final isDesktop = size.width >= 900;
    
    if (isDesktop) {
      return Scaffold(
        body: Row(
          children: [
            _buildNavigationRail(context),
            const VerticalDivider(thickness: 1, width: 1),
            Expanded(child: navigationShell),
          ],
        ),
      );
    }

    return PharmResponsiveWrapper(
      showSidePanelDecoration: true,
      child: Scaffold(
        extendBody: true,
        body: navigationShell,
        floatingActionButton: isAiTab
            ? null
            : Container(
                margin: const EdgeInsets.only(bottom: 70),
                child: FloatingActionButton.small(
                  heroTag: 'ai_fab',
                  onPressed: () => AiQuickChatModal.show(context),
                  backgroundColor: Theme.of(context).primaryColor.withOpacity(0.85),
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                    side: BorderSide(color: Theme.of(context).primaryColor.withOpacity(0.2), width: 1),
                  ),
                  child: Icon(Icons.auto_awesome, color: Theme.of(context).scaffoldBackgroundColor, size: 20),
                ),
              ),
        bottomNavigationBar: _buildPremiumBottomNav(context),
      ),
    );
  }

  Widget _buildNavigationRail(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return NavigationRail(
      selectedIndex: widget.navigationShell.currentIndex,
      onDestinationSelected: (index) => _onTap(context, index),
      labelType: NavigationRailLabelType.all,
      backgroundColor: isDark ? const Color(0xFF0D1219) : Colors.white,
      indicatorColor: Theme.of(context).primaryColor.withOpacity(0.1),
      selectedIconTheme: IconThemeData(color: Theme.of(context).primaryColor, size: 28),
      unselectedIconTheme: IconThemeData(color: Theme.of(context).textTheme.bodySmall?.color, size: 24),
      selectedLabelTextStyle: PharmTextStyles.label.copyWith(
        color: Theme.of(context).primaryColor,
        fontWeight: FontWeight.bold,
      ),
      unselectedLabelTextStyle: PharmTextStyles.label.copyWith(
        color: Theme.of(context).textTheme.bodySmall?.color,
      ),
      leading: Padding(
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Image.asset('assets/images/Pharmvrlogo.png', height: 48),
      ),
      destinations: [
        NavigationRailDestination(
          icon: const Icon(Icons.home_outlined),
          selectedIcon: const Icon(Icons.home),
          label: Text(l10n.home),
        ),
        NavigationRailDestination(
          icon: const Icon(Icons.menu_book_outlined),
          selectedIcon: const Icon(Icons.menu_book),
          label: Text(l10n.education),
        ),
        NavigationRailDestination(
          icon: const Icon(Icons.auto_awesome_outlined),
          selectedIcon: const Icon(Icons.auto_awesome),
          label: Text(l10n.pharmai),
        ),
        NavigationRailDestination(
          icon: const Icon(Icons.newspaper_outlined),
          selectedIcon: const Icon(Icons.newspaper),
          label: Text(l10n.news),
        ),
        NavigationRailDestination(
          icon: const Icon(Icons.person_outline),
          selectedIcon: const Icon(Icons.person),
          label: Text(l10n.profile),
        ),
      ],
    );
  }

  Widget _buildPremiumBottomNav(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    
    return ClipRRect(
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 24, sigmaY: 24), // Increased Blur
        child: Container(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).padding.bottom + 12,
            top: 12,
            left: 16,
            right: 16,
          ),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface.withOpacity(0.85),
            border: Border(
              top: BorderSide(color: Theme.of(context).primaryColor.withOpacity(0.15), width: 0.5), // Sharper top edge
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildNavItem(context, 0, Icons.home_outlined, Icons.home, l10n.home),
              _buildNavItem(context, 1, Icons.menu_book_outlined, Icons.menu_book, l10n.education),
              _buildNavItem(context, 2, Icons.auto_awesome_outlined, Icons.auto_awesome, l10n.pharmai),
              _buildNavItem(context, 3, Icons.newspaper_outlined, Icons.newspaper, l10n.news),
              _buildNavItem(context, 4, Icons.person_outline, Icons.person, l10n.profile),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(BuildContext context, int index, IconData unselectedIcon, IconData selectedIcon, String label) {
    final bool isSelected = widget.navigationShell.currentIndex == index;

    return GestureDetector(
      onTap: () => _onTap(context, index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeOutQuint,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? Theme.of(context).primaryColor.withOpacity(0.12) : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? Theme.of(context).primaryColor.withOpacity(0.25) : Colors.transparent,
            width: 1,
          ),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isSelected ? selectedIcon : unselectedIcon,
              color: isSelected ? Theme.of(context).primaryColor : Theme.of(context).textTheme.bodySmall?.color,
              size: isSelected ? 26 : 24,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: PharmTextStyles.label.copyWith(
                color: isSelected ? Theme.of(context).primaryColor : Theme.of(context).textTheme.labelSmall?.color,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                fontSize: 10,
              ),
            ),
            // Subtle glowing dot indicator
            if (isSelected) ...[
              const SizedBox(height: 4),
              Container(
                width: 4,
                height: 4,
                decoration: BoxDecoration(
                  color: Theme.of(context).primaryColor,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Theme.of(context).primaryColor.withOpacity(0.4),
                      blurRadius: 4,
                      spreadRadius: 1,
                    )
                  ],
                ),
              )
            ]
          ],
        ),
      ),
    );
  }
}
