import 'package:flutter/material.dart';
import '../theme/pharm_colors.dart';

class PharmLoadingIndicator extends StatelessWidget {
  final double size;
  final Color color;

  const PharmLoadingIndicator({
    super.key,
    this.size = 40.0,
    this.color = PharmColors.primary,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SizedBox(
        width: size,
        height: size,
        child: CircularProgressIndicator(
          color: color,
          strokeWidth: 3,
        ),
      ),
    );
  }
}
