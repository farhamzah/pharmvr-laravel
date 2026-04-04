import 'package:flutter/material.dart';
import '../../theme/pharm_colors.dart';
import '../../theme/pharm_spacing.dart';
import '../states/pharm_empty_state.dart';

class PharmListScaffold<T> extends StatelessWidget {
  final String title;
  final List<T> items;
  final Widget Function(BuildContext, int, T) itemBuilder;
  final Future<void> Function() onRefresh;
  final String emptyTitle;
  final String emptyMessage;

  const PharmListScaffold({
    super.key,
    required this.title,
    required this.items,
    required this.itemBuilder,
    required this.onRefresh,
    this.emptyTitle = 'No Items Found',
    this.emptyMessage = 'There is nothing to display here right now.',
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: items.isEmpty
          ? PharmEmptyState.noData(title: emptyTitle, message: emptyMessage)
          : RefreshIndicator(
            color: Theme.of(context).primaryColor,
            backgroundColor: Theme.of(context).colorScheme.surface,
            onRefresh: onRefresh,
              child: ListView.separated(
                padding: PharmSpacing.allLg,
                itemCount: items.length,
                separatorBuilder: (context, index) => const SizedBox(height: PharmSpacing.md),
                itemBuilder: (context, index) => itemBuilder(context, index, items[index]),
              ),
            ),
    );
  }
}
