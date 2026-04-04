import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/widgets/pharm_responsive_wrapper.dart';
import '../../../ai_assistant/presentation/widgets/ai_quick_chat_modal.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';

class MainShellScreen extends StatelessWidget {
  final StatefulNavigationShell navigationShell;

  const MainShellScreen({
    super.key,
    required this.navigationShell,
  });

  void _onTap(BuildContext context, int index) {
    navigationShell.goBranch(
      index,
      initialLocation: true,
    );
  }

  @override
  Widget build(BuildContext context) {
    final isAiTab = navigationShell.currentIndex == 2; // PharmAI tab
    
    return PharmResponsiveWrapper(
      showSidePanelDecoration: true,
      child: Scaffold(
        extendBody: true,
        body: navigationShell,
        // Floating AI button — hidden when on AI tab
        floatingActionButton: isAiTab
            ? null
            : Container(
                margin: const EdgeInsets.only(bottom: 70),
                child: FloatingActionButton.small(
                  heroTag: 'ai_fab',
                  onPressed: () => AiQuickChatModal.show(context),
                  backgroundColor: Theme.of(context).primaryColor.withOpacity(0.85),
                  elevation: 2, // Reduced from 4
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
            color: Theme.of(context).colorScheme.surface.withValues(alpha: 0.85),
            border: Border(
              top: BorderSide(color: Theme.of(context).primaryColor.withValues(alpha: 0.15), width: 0.5), // Sharper top edge
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
    final bool isSelected = navigationShell.currentIndex == index;

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
