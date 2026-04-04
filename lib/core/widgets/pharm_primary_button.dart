import 'package:flutter/material.dart';
import '../theme/pharm_colors.dart';

class PharmPrimaryButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? icon;
  final bool isOutlined;

  const PharmPrimaryButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.icon,
    this.isOutlined = false,
  });

  @override
  Widget build(BuildContext context) {
    final bool isDisabled = onPressed == null;
    
    return Container(
      width: double.infinity,
      height: 56,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        border: isOutlined ? Border.all(color: PharmColors.primary, width: 2) : null,
        boxShadow: (isDisabled || isOutlined)
            ? []
            : [
                BoxShadow(
                  color: PharmColors.accentGlow,
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
        gradient: (isDisabled || isOutlined)
            ? null
            : const LinearGradient(
                colors: [PharmColors.primary, PharmColors.primaryDark],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
        color: isOutlined ? Colors.transparent : (isDisabled ? PharmColors.surface : null),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: isLoading ? null : onPressed,
          child: Center(
            child: isLoading
                ? const SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(
                      color: PharmColors.background,
                      strokeWidth: 2,
                    ),
                  )
                : Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      if (icon != null) ...[
                        Icon(
                          icon,
                          color: isOutlined ? PharmColors.primary : PharmColors.background,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                      ],
                      Text(
                        text.toUpperCase(),
                        style: Theme.of(context).textTheme.labelLarge?.copyWith(
                              color: isOutlined 
                                  ? PharmColors.primary 
                                  : (isDisabled ? PharmColors.textSecondary : PharmColors.background),
                              fontWeight: FontWeight.bold,
                              letterSpacing: 1.1,
                            ),
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }
}

