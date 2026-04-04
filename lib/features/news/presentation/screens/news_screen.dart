import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';

import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/widgets/pharm_loading_indicator.dart';
import '../../../../core/widgets/states/pharm_empty_state.dart';
import '../../../../core/widgets/states/pharm_error_state.dart';
import '../providers/news_provider.dart';
import '../widgets/pharm_news_card.dart';

class NewsScreen extends ConsumerStatefulWidget {
  const NewsScreen({super.key});

  @override
  ConsumerState<NewsScreen> createState() => _NewsScreenState();
}

class _NewsScreenState extends ConsumerState<NewsScreen> {
  final List<String> _topics = ['AI', 'VR/XR', 'GMP', 'Pharma Industry', 'Digital Health'];

  @override
  Widget build(BuildContext context) {
    final newsState = ref.watch(newsProvider);
    final filter = ref.watch(newsFilterProvider);
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text(l10n.industryNews, style: PharmTextStyles.h4),
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        elevation: 0,
        centerTitle: false,
      ),
      body: Column(
        children: [
          // Filter Section
          Container(
            padding: const EdgeInsets.symmetric(vertical: PharmSpacing.md),
            decoration: const BoxDecoration(
              color: Colors.transparent,
              border: Border(bottom: BorderSide(color: PharmColors.divider)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Type Filter
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg),
                  physics: const BouncingScrollPhysics(),
                  child: Row(
                    children: [
                      _buildTypeFilter(context, label: 'All Intelligence', value: null, current: filter.type),
                      const SizedBox(width: 8),
                      _buildTypeFilter(context, label: 'Internal News', value: 'internal', current: filter.type),
                      const SizedBox(width: 8),
                      _buildTypeFilter(context, label: 'Curated External', value: 'external', current: filter.type),
                    ],
                  ),
                ),
                const SizedBox(height: PharmSpacing.md),
                // Topic Filter
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg),
                  physics: const BouncingScrollPhysics(),
                  child: Row(
                    children: [
                      _buildTopicFilter(context, label: 'All Topics', value: null, current: filter.topicCategory),
                      ..._topics.map((topic) => Padding(
                            padding: const EdgeInsets.only(left: 8.0),
                            child: _buildTopicFilter(context, label: topic, value: topic, current: filter.topicCategory),
                          )),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Content Section
          Expanded(
            child: RefreshIndicator(
              onRefresh: () => ref.read(newsProvider.notifier).refresh(),
              color: PharmColors.primary,
              backgroundColor: PharmColors.surfaceLight,
              child: newsState.when(
                loading: () => const Center(child: PharmLoadingIndicator()),
                error: (error, stack) => PharmErrorState.generic(
                  message: error.toString(),
                  onRetry: () => ref.read(newsProvider.notifier).refresh(),
                ),
                data: (articles) {
                  if (articles.isEmpty) {
                    return SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      child: Container(
                        height: MediaQuery.of(context).size.height * 0.5,
                        alignment: Alignment.center,
                        child: PharmEmptyState.noData(
                          title: l10n.noNewsAvailable,
                          message: l10n.newsEmptyMessage,
                        ),
                      ),
                    );
                  }
                  
                  return ListView.separated(
                    padding: const EdgeInsets.all(PharmSpacing.lg),
                    physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
                    itemCount: articles.length,
                    separatorBuilder: (context, index) => const SizedBox(height: PharmSpacing.lg),
                    itemBuilder: (context, index) {
                      final article = articles[index];
                      return PharmNewsCard(
                        article: article,
                        onTap: () {
                          if (article.isExternal) {
                            context.push('/news/external/${article.slug}');
                          } else {
                            context.push('/news/detail/${article.slug}');
                          }
                        },
                      );
                    },
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTypeFilter(BuildContext context, {required String label, required String? value, required String? current}) {
    final isSelected = current == value;
    return InkWell(
      onTap: () {
        ref.read(newsFilterProvider.notifier).updateState(
          ref.read(newsFilterProvider).copyWith(type: value, clearType: value == null)
        );
        ref.read(newsProvider.notifier).refresh();
      },
      borderRadius: BorderRadius.circular(24),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? PharmColors.primary : Colors.transparent,
          border: Border.all(color: isSelected ? PharmColors.primary : PharmColors.divider),
          borderRadius: BorderRadius.circular(24),
        ),
        child: Text(
          label,
          style: PharmTextStyles.button.copyWith(
            color: isSelected ? PharmColors.background : PharmColors.textSecondary,
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
          ),
        ),
      ),
    );
  }

  Widget _buildTopicFilter(BuildContext context, {required String label, required String? value, required String? current}) {
    final isSelected = current == value;
    return InkWell(
      onTap: () {
        ref.read(newsFilterProvider.notifier).updateState(
          ref.read(newsFilterProvider).copyWith(topicCategory: value, clearTopicCategory: value == null)
        );
        ref.read(newsProvider.notifier).refresh();
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? PharmColors.primary.withOpacity(0.15) : PharmColors.surfaceLight,
          border: Border.all(color: isSelected ? PharmColors.primary.withOpacity(0.5) : Colors.transparent),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Text(
          label,
          style: PharmTextStyles.label.copyWith(
            color: isSelected ? PharmColors.primary : PharmColors.textSecondary,
            fontSize: 11,
          ),
        ),
      ),
    );
  }
}
