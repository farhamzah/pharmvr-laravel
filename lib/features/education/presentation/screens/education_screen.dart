import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/widgets/pharm_loading_indicator.dart';
import '../../../../core/widgets/states/pharm_error_state.dart';
import '../../../../core/widgets/states/pharm_empty_state.dart';
import '../providers/education_provider.dart';
import '../widgets/pharm_edukasi_card.dart';
import '../../domain/models/learning_module.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';

class EducationScreen extends ConsumerStatefulWidget {
  const EducationScreen({super.key});

  @override
  ConsumerState<EducationScreen> createState() => _EducationScreenState();
}

class _EducationScreenState extends ConsumerState<EducationScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final edukasiState = ref.watch(edukasiProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.educationCenter),
        backgroundColor: Colors.transparent,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: PharmColors.primary,
          labelColor: PharmColors.primary,
          unselectedLabelColor: Theme.of(context).textTheme.bodySmall?.color,
          tabs: [
            Tab(text: AppLocalizations.of(context)!.trainingModule, icon: const Icon(Icons.vrpano_outlined)),
            Tab(text: AppLocalizations.of(context)!.educationalVideo, icon: const Icon(Icons.play_circle_outline)),
            Tab(text: AppLocalizations.of(context)!.document, icon: const Icon(Icons.description_outlined)),
          ],
        ),
      ),
      body: SafeArea(
        child: edukasiState.when(
          loading: () => const Center(child: PharmLoadingIndicator()),
          error: (error, stack) => PharmErrorState.generic(
            message: error.toString(),
            onRetry: () => ref.read(edukasiProvider.notifier).refresh(),
          ),
          data: (items) {
            return RefreshIndicator(
              key: const ValueKey('education_refresh'),
              color: PharmColors.primary,
              backgroundColor: Theme.of(context).colorScheme.surface,
              onRefresh: () => ref.read(edukasiProvider.notifier).refresh(),
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildList(context, ref, 'module'),
                  _buildList(context, ref, 'video'),
                  _buildList(context, ref, 'document'),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildList(BuildContext context, WidgetRef ref, String type) {
    final notifier = ref.read(edukasiProvider.notifier);
    final filteredItems = notifier.getItemsByType(type);

    if (filteredItems.isEmpty) {
      final l10n = AppLocalizations.of(context)!;
      String typeLabel = type == 'module' 
          ? l10n.trainingModule 
          : (type == 'video' ? l10n.educationalVideo : l10n.document);
      
      return PharmEmptyState.noData(
        title: l10n.empty,
        message: l10n.noMaterialsAvailable(typeLabel.toLowerCase()),
      );
    }

    final isDesktop = MediaQuery.of(context).size.width >= 900;

    if (type == 'video' || isDesktop) {
      return GridView.builder(
        padding: PharmSpacing.allLg,
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: isDesktop ? 3 : 2,
          crossAxisSpacing: PharmSpacing.lg,
          mainAxisSpacing: PharmSpacing.lg,
          childAspectRatio: type == 'video' ? 0.85 : (type == 'module' ? 0.95 : 1.0),
        ),
        itemCount: filteredItems.length,
        itemBuilder: (context, index) {
          final item = filteredItems[index];
          return PharmEdukasiCard(
            module: item,
            isGrid: true,
            onTap: () => context.push('/education/detail/${item.slug}'),
          );
        },
      );
    }

    return ListView.separated(
      padding: PharmSpacing.allLg,
      itemCount: filteredItems.length,
      separatorBuilder: (context, index) => const SizedBox(height: PharmSpacing.md),
      itemBuilder: (context, index) {
        final item = filteredItems[index];
        return PharmEdukasiCard(
          module: item,
          onTap: () {
            context.push('/education/detail/${item.slug}');
          },
        );
      },
    );
  }
}
