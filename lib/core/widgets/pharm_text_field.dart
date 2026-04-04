import 'package:flutter/material.dart';
import '../theme/pharm_colors.dart';
import '../theme/pharm_text_styles.dart';

class PharmTextField extends StatefulWidget {
  final TextEditingController controller;
  final String labelText;
  final String? hintText;
  final bool obscureText;
  final bool isPasswordField;
  final TextInputType keyboardType;
  final TextInputAction textInputAction;
  final FocusNode? focusNode; // New
  final Iterable<String>? autofillHints; // New
  final String? Function(String?)? validator;
  final Widget? prefixIcon;
  final Widget? suffixIcon;
  final ValueChanged<String>? onFieldSubmitted;

  const PharmTextField({
    super.key,
    required this.controller,
    required this.labelText,
    this.hintText,
    this.obscureText = false,
    this.isPasswordField = false,
    this.keyboardType = TextInputType.text,
    this.textInputAction = TextInputAction.next,
    this.focusNode,
    this.autofillHints,
    this.validator,
    this.prefixIcon,
    this.suffixIcon,
    this.onFieldSubmitted,
  });

  @override
  State<PharmTextField> createState() => _PharmTextFieldState();
}

class _PharmTextFieldState extends State<PharmTextField> {
  late bool _obscureText;

  @override
  void initState() {
    super.initState();
    // Use initial obscureText value, or true if it's a password field
    _obscureText = widget.isPasswordField || widget.obscureText;
  }

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: widget.controller,
      obscureText: _obscureText,
      keyboardType: widget.keyboardType,
      textInputAction: widget.textInputAction,
      focusNode: widget.focusNode,
      autofillHints: widget.autofillHints,
      validator: widget.validator,
      onFieldSubmitted: widget.onFieldSubmitted,
      style: Theme.of(context).textTheme.bodyLarge?.copyWith(
        color: PharmColors.textPrimary,
        fontWeight: FontWeight.w600,
      ),
      cursorColor: PharmColors.primary,
      decoration: InputDecoration(
        labelText: widget.labelText,
        hintText: widget.hintText,
        prefixIcon: widget.prefixIcon != null 
          ? IconTheme(
              data: IconThemeData(
                color: Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.7),
                size: 20,
              ),
              child: widget.prefixIcon!,
            )
          : null,
        suffixIcon: widget.isPasswordField 
          ? IconButton(
              icon: Icon(
                _obscureText ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                color: PharmColors.primary, // Explicitly use primary color for visibility
                size: 20,
              ),
              onPressed: () {
                setState(() {
                  _obscureText = !_obscureText;
                });
              },
            )
          : widget.suffixIcon,
        filled: true,
        fillColor: Theme.of(context).brightness == Brightness.dark 
            ? PharmColors.surfaceLight.withOpacity(0.4) 
            : PharmColors.backgroundLight,
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
        labelStyle: PharmTextStyles.bodyMedium.copyWith(
          color: Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.9),
          fontWeight: FontWeight.w600,
        ),
        hintStyle: PharmTextStyles.bodyMedium.copyWith(
          color: Theme.of(context).textTheme.labelSmall?.color?.withOpacity(0.4),
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: Theme.of(context).dividerColor.withOpacity(0.1)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: Theme.of(context).dividerColor.withOpacity(0.1)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.primary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.error, width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.error, width: 2),
        ),
      ),
    );
  }
}
