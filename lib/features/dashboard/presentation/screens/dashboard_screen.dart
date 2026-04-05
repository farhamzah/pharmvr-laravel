import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/widgets/pharm_loading_indicator.dart';
import '../../../../core/widgets/pharm_training_journey.dart';
import '../../../../core/widgets/states/pharm_error_state.dart';
import '../providers/dashboard_provider.dart';
import '../../domain/models/home_hub.dart';
import '../../../news/domain/models/news_article.dart';
import '../../../vr_experience/presentation/providers/vr_connection_provider.dart';
import '../../../vr_experience/presentation/providers/vr_readiness_provider.dart';
import '../../../../core/config/network_constants.dart';
import '../../../../core/widgets/pharm_network_image.dart';
import '../../../../core/widgets/pharm_network_avatar.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';
import 'package:cached_network_image/cached_network_image.dart';

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final dashboardState = ref.watch(dashboardProvider);
    final vrState = ref.watch(vrConnectionProvider);

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: SafeArea(
        child: dashboardState.when(
          loading: () => const Center(child: PharmLoadingIndicator()),
          error: (error, stack) => PharmErrorState.generic(
            message: error.toString(),
            onRetry: () => ref.read(dashboardProvider.notifier).refresh(),
          ),
          data: (data) => RefreshIndicator(
            color: Theme.of(context).primaryColor,
            backgroundColor: Theme.of(context).colorScheme.surface,
            onRefresh: () async => ref.read(dashboardProvider.notifier).refresh(),
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _HeroSection(data: data, vrState: vrState),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _SectionHeader(title: AppLocalizations.of(context)!.yourProgress),
                        _ProgressGrid(data: data),
                        const SizedBox(height: 28),
                        _SectionHeader(title: AppLocalizations.of(context)!.trainingJourney),
                        Consumer(
                          builder: (context, ref, _) {
                            final slug = data.heroModuleCard?.code ?? "";
                            final readiness = ref.watch(vrReadinessProvider);
                            
                            // Auto-fetch if not loaded
                            if (readiness.checklist.isEmpty && !readiness.isLoading && slug.isNotEmpty) {
                              Future.microtask(() => ref.read(vrReadinessProvider.notifier).fetchReadiness(slug));
                            }

                            return PharmTrainingJourney(
                              currentStep: data.heroModuleCard?.currentStep, 
                              moduleTitle: data.heroModuleCard?.title,
                              onPreTest: () => context.push('/assessment/intro/$slug/pre'),
                              onLaunchVr: () => context.push('/vr/launch'),
                              onPostTest: () => context.push('/assessment/intro/$slug/post'),
                            );
                          },
                        ),
                        const SizedBox(height: 28),
                        _SectionHeader(title: AppLocalizations.of(context)!.quickActions),
                        _QuickActions(data: data),
                        const SizedBox(height: 28),
                        
                        _SectionHeader(title: AppLocalizations.of(context)!.featuredLearning),
                        _FeaturedLearning(module: data.featuredLearningPreview),
                        const SizedBox(height: 28),

                        if (data.latestNewsPreview.isNotEmpty) ...[
                          _NewsCard(news: data.latestNewsPreview.first),
                          const SizedBox(height: 16),
                        ],
                        _AiCard(actions: data.smartActions),
                        const SizedBox(height: 100),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ───────────────────────────────────────────────────────────
// SHARED: SECTION HEADER
// ───────────────────────────────────────────────────────────
class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16, top: 4),
      child: Text(title, style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
    );
  }
}

// ═══════════════════════════════════════════════════════════
// 1.  HERO SECTION — Banner + Greeting + Module Card
// ═══════════════════════════════════════════════════════════
class _HeroSection extends StatelessWidget {
  final HomeHubData data;
  final VrConnectionState vrState;
  const _HeroSection({required this.data, required this.vrState});

  @override
  Widget build(BuildContext context) {
    final fullName = data.userGreeting['full_name'] as String? ?? 'User';
    final academicSummary = data.userGreeting['academic_summary'] as String? ?? '';
    final avatarUrl = data.userGreeting['avatar_url'] as String?;

    return Column(
      children: [
        // Banner image (Using a default or if available in greeting)
        _BannerImage(url: data.bannerUrl),

        // Greeting row
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
          child: Row(
            children: [
              // Avatar
              PharmNetworkAvatar(
                url: avatarUrl,
                displayName: fullName,
                size: 48,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(AppLocalizations.of(context)!.welcomeBack, style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
                    Text(fullName, style: PharmTextStyles.h3.copyWith(color: Theme.of(context).textTheme.displaySmall?.color, fontSize: 18)),
                    if (academicSummary.isNotEmpty)
                      Text(academicSummary, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).primaryColor, fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
              _VrPill(status: vrState.status),
            ],
          ),
        ),

        // Current module card
        if (data.heroModuleCard != null) ...[
          const SizedBox(height: 18),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: _CurrentModuleCard(module: data.heroModuleCard!),
          ),
        ],
        const SizedBox(height: 24),
      ],
    );
  }
}

// ── Banner Image ──
class _BannerImage extends StatelessWidget {
  final String? url;
  const _BannerImage({this.url});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 150,
      width: double.infinity,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (url != null)
            PharmNetworkImage(
              url: NetworkConstants.sanitizeUrl(url!),
              fit: BoxFit.cover,
              errorWidget: _fallback(context),
            )
          else
            _fallback(context),
          // Bottom fade
          Positioned.fill(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  stops: const [0.0, 0.45, 1.0],
                  colors: [Colors.transparent, Theme.of(context).scaffoldBackgroundColor.withOpacity(0.5), Theme.of(context).scaffoldBackgroundColor],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _fallback(BuildContext context, {bool loading = false}) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        image: const DecorationImage(
          image: AssetImage('assets/images/web_landing_hero.png'),
          fit: BoxFit.cover,
          opacity: 0.3, // Subtle opacity so it doesn't distract from text
        ),
      ),
      child: loading
          ? const Center(child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: PharmColors.primary, strokeWidth: 2)))
          : Center(child: Icon(Icons.biotech, size: 40, color: PharmColors.primary.withOpacity(0.15))),
    );
  }
}

// ── VR Status Pill ──
class _VrPill extends StatelessWidget {
  final VrConnectionStatus status;
  const _VrPill({required this.status});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final (Color c, IconData ic, String lbl) = switch (status) {
      VrConnectionStatus.ready    => (PharmColors.success, Icons.headset, l10n.vrReady),
      VrConnectionStatus.inProgress => (PharmColors.info, Icons.play_circle_fill, l10n.vrActive),
      VrConnectionStatus.pairing  => (PharmColors.warning, Icons.sync, l10n.vrSyncing),
      VrConnectionStatus.offline  => (Theme.of(context).textTheme.bodySmall?.color ?? PharmColors.textTertiary, Icons.headset_off, l10n.vrDisconnected),
      VrConnectionStatus.idle     => (Theme.of(context).textTheme.bodySmall?.color ?? PharmColors.textTertiary, Icons.headset_off, l10n.vrIdle),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: c.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: c.withOpacity(0.25)),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(ic, color: c, size: 14),
        const SizedBox(width: 6),
        Text(lbl, style: PharmTextStyles.caption.copyWith(color: c, fontWeight: FontWeight.w600)),
      ]),
    );
  }
}

// ── Current Module Card ──
class _CurrentModuleCard extends StatelessWidget {
  final HeroModuleCard module;
  const _CurrentModuleCard({required this.module});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          context.push('/education/detail/${module.id}');
        },
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: PharmColors.primary.withOpacity(0.1)),
            boxShadow: [BoxShadow(color: PharmColors.primary.withOpacity(0.05), blurRadius: 20, offset: const Offset(0, 6))],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: Theme.of(context).primaryColor.withOpacity(0.12), borderRadius: BorderRadius.circular(20)),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    Icon(Icons.vrpano, color: Theme.of(context).primaryColor, size: 13),
                    const SizedBox(width: 5),
                    Text(AppLocalizations.of(context)!.currentModule, style: PharmTextStyles.overline.copyWith(color: Theme.of(context).primaryColor, fontWeight: FontWeight.w800)),
                  ]),
                ),
                const Spacer(),
                Text(module.code, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
              ]),
              const SizedBox(height: 14),
              Text(module.title, style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
              const SizedBox(height: 5),
              Text(module.description, style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color), maxLines: 2, overflow: TextOverflow.ellipsis),
              const SizedBox(height: 14),
              Row(
                children: [
                  Icon(Icons.timer_outlined, size: 14, color: Theme.of(context).textTheme.labelSmall?.color),
                  const SizedBox(width: 4),
                  Text(module.estimatedDuration, style: PharmTextStyles.caption),
                  const SizedBox(width: 16),
                  Icon(Icons.bar_chart, size: 14, color: Theme.of(context).textTheme.labelSmall?.color),
                  const SizedBox(width: 4),
                  Text(module.difficulty, style: PharmTextStyles.caption),
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
// 2.  PROGRESS — 2×2 grid (inspired by reference)
// ═══════════════════════════════════════════════════════════
class _ProgressGrid extends StatelessWidget {
  final HomeHubData data;
  const _ProgressGrid({required this.data});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final summary = data.progressSummary;
    final progress = (summary['progress_percentage'] as num?)?.toDouble() ?? 0.0;
    
    final isDesktop = MediaQuery.of(context).size.width >= 900;
    
    if (isDesktop) {
      return Row(children: [
        _StatCard(icon: Icons.library_books_outlined, value: '${summary['completed_modules']}/${summary['total_modules']}', label: l10n.modules, color: PharmColors.primary),
        const SizedBox(width: 12),
        _StatCard(icon: Icons.view_in_ar, value: '${summary['vr_sessions_count']}', label: l10n.vrSessions, color: PharmColors.info),
        const SizedBox(width: 12),
        _StatCard(icon: Icons.speed, value: '${progress.toStringAsFixed(0)}%', label: l10n.avgScore, color: progress > 80 ? PharmColors.success : PharmColors.warning),
        const SizedBox(width: 12),
        _StatCard(icon: Icons.diamond_outlined, value: '${summary['total_xp']}', label: l10n.xpGained, color: PharmColors.warning),
      ]);
    }

    return Column(
      children: [
        Row(children: [
          _StatCard(icon: Icons.library_books_outlined, value: '${summary['completed_modules']}/${summary['total_modules']}', label: l10n.modules, color: PharmColors.primary),
          const SizedBox(width: 12),
          _StatCard(icon: Icons.view_in_ar, value: '${summary['vr_sessions_count']}', label: l10n.vrSessions, color: PharmColors.info),
        ]),
        const SizedBox(height: 12),
        Row(children: [
          _StatCard(icon: Icons.speed, value: '${progress.toStringAsFixed(0)}%', label: l10n.avgScore, color: progress > 80 ? PharmColors.success : PharmColors.warning),
          const SizedBox(width: 12),
          _StatCard(icon: Icons.diamond_outlined, value: '${summary['total_xp']}', label: l10n.xpGained, color: PharmColors.warning),
        ]),
      ],
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String value;
  final String label;
  final Color color;
  const _StatCard({required this.icon, required this.value, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.fromLTRB(16, 18, 16, 18),
          decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: Theme.of(context).brightness == Brightness.dark 
                ? color.withOpacity(0.15) 
                : color.withOpacity(0.3),
            width: Theme.of(context).brightness == Brightness.dark ? 1.0 : 0.5,
          ),
          boxShadow: [
            BoxShadow(
              color: Theme.of(context).brightness == Brightness.dark 
                  ? color.withOpacity(0.06) 
                  : Colors.black.withOpacity(0.04),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [color.withOpacity(0.15), color.withOpacity(0.05)],
                ),
                border: Border.all(color: color.withOpacity(0.1)),
              ),
              child: Icon(icon, size: 22, color: color),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(value, style: PharmTextStyles.h3.copyWith(color: Theme.of(context).textTheme.displaySmall?.color, fontSize: 18)),
                  const SizedBox(height: 2),
                  Text(label, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}



// ═══════════════════════════════════════════════════════════
// 4.  QUICK ACTIONS — 3 cards with large rounded-square icons
// ═══════════════════════════════════════════════════════════
class _QuickActions extends ConsumerWidget {
  final HomeHubData data;
  const _QuickActions({required this.data});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final status = data.vrStatusHeader;
    final vrState = ref.watch(vrConnectionProvider);

    final l10n = AppLocalizations.of(context)!;

    return Row(children: [
      _ActionTile(
        icon: Icons.play_circle_filled,
        label: l10n.continueTraining,
        color: PharmColors.success,
        onTap: () => context.push(status.recommendedNextRoute),
      ),
      const SizedBox(width: 12),
      _ActionTile(
        icon: Icons.vrpano,
        label: l10n.connectVr,
        color: PharmColors.primary,
        isHero: !vrState.isPaired,
        onTap: () => context.push('/vr/connect'),
      ),
      const SizedBox(width: 12),
      _ActionTile(
        icon: Icons.auto_awesome,
        label: l10n.askAi,
        color: Theme.of(context).primaryColor,
        onTap: () => context.go('/ai-assistant'),
      ),
    ]);
  }
}

class _ActionTile extends StatefulWidget {
  final IconData icon;
  final String label;
  final Color color;
  final bool isHero;
  final VoidCallback onTap;
  const _ActionTile({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
    this.isHero = false,
  });

  @override
  State<_ActionTile> createState() => _ActionTileState();
}

class _ActionTileState extends State<_ActionTile> with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    );
    if (widget.isHero) {
      _pulseController.repeat(reverse: true);
    }
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: widget.onTap,
          borderRadius: BorderRadius.circular(18),
          child: AnimatedBuilder(
            animation: _pulseController,
            builder: (context, child) {
              return Container(
                padding: const EdgeInsets.symmetric(vertical: 22),
                decoration: BoxDecoration(
                  color: widget.isHero
                      ? PharmColors.primary.withOpacity(0.08 + (0.04 * _pulseController.value))
                      : Theme.of(context).colorScheme.surface,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: widget.isHero
                        ? PharmColors.primary.withOpacity(0.3 + (0.2 * _pulseController.value))
                        : (Theme.of(context).brightness == Brightness.dark
                            ? PharmColors.cardBorder.withOpacity(0.5)
                            : PharmColors.dividerLight),
                    width: widget.isHero ? 1.5 : 1,
                  ),
                  boxShadow: widget.isHero
                      ? [
                          BoxShadow(
                            color: Theme.of(context).primaryColor.withOpacity(0.2 * _pulseController.value),
                            blurRadius: 16 + (8 * _pulseController.value),
                            spreadRadius: 1 + (2 * _pulseController.value),
                          )
                        ]
                      : [
                          if (Theme.of(context).brightness == Brightness.light)
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 12,
                              offset: const Offset(0, 4),
                            )
                        ],
                ),
                child: Column(children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      color: widget.color.withOpacity(0.12),
                    ),
                    child: Icon(widget.icon, color: widget.color, size: 26),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    widget.label,
                    textAlign: TextAlign.center,
                    style: PharmTextStyles.label.copyWith(
                      color: widget.isHero
                          ? PharmColors.primary
                          : Theme.of(context).textTheme.bodySmall?.color,
                      fontWeight: FontWeight.w700,
                      height: 1.3,
                    ),
                  ),
                ]),
              );
            },
          ),
        ),
      ),
    );
  }
}


// ═══════════════════════════════════════════════════════════
// 5.  LATEST NEWS  +  6. AI SUGGESTION
// ═══════════════════════════════════════════════════════════
class _NewsCard extends StatelessWidget {
  final NewsArticle news;
  const _NewsCard({required this.news});

  @override
  Widget build(BuildContext context) {
    return _InfoRow(
      onTap: () => context.push('/news/detail/${news.slug}'),
      icon: Icons.newspaper, iconColor: PharmColors.info,
      overline: AppLocalizations.of(context)!.latestNews,
      text: news.title,
    );
  }
}

class _AiCard extends StatelessWidget {
  final List<dynamic> actions;
  const _AiCard({required this.actions});

  @override
  Widget build(BuildContext context) {
    final firstAction = actions.isNotEmpty ? actions.first as Map<String, dynamic> : null;
    
    return _InfoRow(
      onTap: () => context.go('/ai-assistant'),
      icon: Icons.auto_awesome, iconColor: Theme.of(context).primaryColor,
      overline: AppLocalizations.of(context)!.pharmAiSuggests,
      text: firstAction != null 
          ? firstAction['label'] ?? 'Ask AI about CPOB/GMP best practices'
          : 'Ask AI about CPOB/GMP best practices',
      tinted: true,
    );
  }
}

class _FeaturedLearning extends StatelessWidget {
  final Map<String, dynamic> module;
  const _FeaturedLearning({required this.module});

  @override
  Widget build(BuildContext context) {
    final firstMod = module['modul'] as Map<String, dynamic>?;
    
    return _InfoRow(
      onTap: () => firstMod != null ? context.push('/education/detail/${firstMod['id']}') : null,
      icon: Icons.school_outlined, iconColor: PharmColors.success,
      overline: AppLocalizations.of(context)!.featuredLearning,
      text: firstMod != null ? (firstMod['title'] ?? 'Explore more modules') : 'Explore more modules',
    );
  }
}

class _InfoRow extends StatelessWidget {
  final VoidCallback onTap;
  final IconData icon;
  final Color iconColor;
  final String overline;
  final String text;
  final bool tinted;
  const _InfoRow({required this.onTap, required this.icon, required this.iconColor,
    required this.overline, required this.text, this.tinted = false});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: tinted ? PharmColors.primary.withOpacity(0.05) : Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: tinted ? PharmColors.primary.withOpacity(0.1) : (Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight)),
          ),
          child: Row(children: [
            Container(
              width: 44, height: 44,
              decoration: BoxDecoration(borderRadius: BorderRadius.circular(12), color: iconColor.withOpacity(0.1)),
              child: Icon(icon, color: iconColor, size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(overline, style: PharmTextStyles.overline.copyWith(color: tinted ? iconColor : Theme.of(context).textTheme.labelSmall?.color)),
                const SizedBox(height: 3),
                Text(text, style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.displaySmall?.color, height: 1.4), maxLines: 2, overflow: TextOverflow.ellipsis),
              ]),
            ),
            const SizedBox(width: 8),
            Icon(Icons.arrow_forward_ios, size: 12, color: tinted ? iconColor : Theme.of(context).textTheme.labelSmall?.color),
          ]),
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════
// 7. CONTINUE LEARNING LIST
// ═══════════════════════════════════════════════════════════
class _ContinueLearningList extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    // Mock horizontal scrolling list of other modules
    final modules = [
      {'id': 'MOD-002', 'title': 'Sterile Gloving', 'progress': 0.8},
      {'id': 'MOD-003', 'title': 'Laminar Airflow Op', 'progress': 0.1},
    ];

    return SizedBox(
      height: 140, // Reduced from standard card size to fit neatly
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        physics: const BouncingScrollPhysics(),
        itemCount: modules.length,
        separatorBuilder: (context, index) => const SizedBox(width: 16),
        itemBuilder: (context, index) {
          final mod = modules[index];
          final progress = mod['progress'] as double;
          return Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: () => context.push('/education/detail/${mod['id']}'),
              borderRadius: BorderRadius.circular(16),
              child: Container(
                width: 240,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: PharmColors.primary.withOpacity(0.08)),
                  boxShadow: [
                    BoxShadow(
                      color: PharmColors.primary.withOpacity(0.04),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(8),
                            color: PharmColors.primary.withOpacity(0.1),
                          ),
                          child: const Icon(Icons.view_in_ar, size: 14, color: PharmColors.primary),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          mod['id'] as String,
                          style: PharmTextStyles.caption.copyWith(
                            color: PharmColors.primary,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Expanded(
                      child: Text(
                        mod['title'] as String,
                        style: PharmTextStyles.subtitle.copyWith(
                          color: Theme.of(context).textTheme.displaySmall?.color,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Row(
                      children: [
                        Expanded(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(99),
                            child: LinearProgressIndicator(
                              value: progress,
                              backgroundColor: Theme.of(context).dividerColor,
                              color: Theme.of(context).colorScheme.primary,
                              minHeight: 5,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '${(progress * 100).toInt()}%',
                          style: PharmTextStyles.caption.copyWith(
                            color: Theme.of(context).textTheme.bodySmall?.color,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
