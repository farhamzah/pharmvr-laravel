import 'package:flutter/material.dart';
import '../../theme/pharm_spacing.dart';
import '../../theme/pharm_colors.dart';

class PharmSettingsScaffold extends StatelessWidget {
  final String title;
  final List<Widget> sections;

  const PharmSettingsScaffold({
    super.key,
    required this.title,
    required this.sections,
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
      ),
      body: SafeArea(
        child: ListView.separated(
          padding: PharmSpacing.allLg,
          itemCount: sections.length,
          separatorBuilder: (context, index) => const SizedBox(height: PharmSpacing.xl),
          itemBuilder: (context, index) => sections[index],
        ),
      ),
    );
  }
}
