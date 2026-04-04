import 'package:flutter/material.dart';
import '../../theme/pharm_colors.dart';
import '../../theme/pharm_spacing.dart';

class PharmDetailScaffold extends StatelessWidget {
  final String title;
  final Widget child;
  final List<Widget>? actions;
  final Widget? floatingActionButton;

  const PharmDetailScaffold({
    super.key,
    required this.title,
    required this.child,
    this.actions,
    this.floatingActionButton,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: PharmColors.primary),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: actions,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: PharmSpacing.allLg,
          child: child,
        ),
      ),
      floatingActionButton: floatingActionButton,
    );
  }
}
