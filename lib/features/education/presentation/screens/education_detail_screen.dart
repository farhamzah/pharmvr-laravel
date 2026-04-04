import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/widgets/pharm_loading_indicator.dart';
import '../../../../core/widgets/states/pharm_error_state.dart';
import '../providers/education_provider.dart';
import '../widgets/education_content_hero.dart';
import '../widgets/education_content_description.dart';
import '../widgets/education_relevance_section.dart';
import '../widgets/education_cta_section.dart';
import '../widgets/education_related_section.dart';

class EducationDetailScreen extends ConsumerWidget {
  final String contentId;

  const EducationDetailScreen({
    super.key,
    required this.contentId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailState = ref.watch(moduleDetailProvider(contentId));

    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text(
          detailState.maybeWhen(
            data: (detail) => detail.module.category,
            orElse: () => 'Detail Modul',
          ),
        ),
        backgroundColor: PharmColors.background,
        elevation: 0,
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.bookmark_border_rounded),
            onPressed: () {},
            tooltip: 'Simpan Modul',
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: detailState.when(
        loading: () => const Center(child: PharmLoadingIndicator()),
        error: (error, stack) => PharmErrorState.generic(
          message: error.toString(),
          onRetry: () => ref.refresh(moduleDetailProvider(contentId)),
        ),
        data: (detail) => SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              EducationContentHero(module: detail.module),
              EducationContentDescription(module: detail.module),
              EducationRelevanceSection(module: detail.module),
              EducationCTASection(module: detail.module),
              EducationRelatedSection(relatedItems: detail.relatedContent),
            ],
          ),
        ),
      ),
    );
  }
}
