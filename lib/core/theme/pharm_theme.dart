import 'package:flutter/material.dart';
import 'pharm_colors.dart';
import 'pharm_text_styles.dart';

class PharmTheme {
  static ThemeData get darkTheme {
    return ThemeData(
      brightness: Brightness.dark,
      scaffoldBackgroundColor: PharmColors.background,
      primaryColor: PharmColors.primary,
      dividerColor: PharmColors.divider,
      colorScheme: const ColorScheme.dark(
        primary: PharmColors.primary,
        secondary: PharmColors.primaryDark,
        surface: PharmColors.surface,
        error: PharmColors.error,
      ),
      textTheme: TextTheme(
        displayLarge: PharmTextStyles.h1.copyWith(color: PharmColors.textPrimary),
        displayMedium: PharmTextStyles.h2.copyWith(color: PharmColors.textPrimary),
        displaySmall: PharmTextStyles.h3.copyWith(color: PharmColors.textPrimary),
        headlineMedium: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary),
        bodyLarge: PharmTextStyles.bodyLarge.copyWith(color: PharmColors.textPrimary),
        bodyMedium: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textPrimary),
        bodySmall: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondary),
        labelLarge: PharmTextStyles.button.copyWith(color: PharmColors.textPrimary),
        labelSmall: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: PharmColors.primary,
          foregroundColor: PharmColors.background,
          textStyle: PharmTextStyles.button,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          elevation: 4,
          shadowColor: PharmColors.accentGlow,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: PharmColors.surface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.primary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.error, width: 2),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.error, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        labelStyle: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary),
        hintStyle: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textTertiary),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: PharmColors.surfaceLight,
        contentTextStyle: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textPrimary),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: PharmColors.surfaceLight,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        titleTextStyle: PharmTextStyles.h3.copyWith(color: PharmColors.textPrimary),
        contentTextStyle: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary),
      ),
      scrollbarTheme: ScrollbarThemeData(
        thumbColor: WidgetStateProperty.all(PharmColors.primary.withOpacity(0.3)),
        trackColor: WidgetStateProperty.all(PharmColors.surfaceLight.withOpacity(0.1)),
        thickness: WidgetStateProperty.all(6),
        radius: const Radius.circular(10),
        thumbVisibility: WidgetStateProperty.all(false),
        interactive: true,
      ),
    );
  }
  static ThemeData get lightTheme {
    return ThemeData(
      brightness: Brightness.light,
      scaffoldBackgroundColor: PharmColors.backgroundLight,
      primaryColor: PharmColors.primary,
      dividerColor: PharmColors.dividerLight,
      colorScheme: const ColorScheme.light(
        primary: PharmColors.primary,
        secondary: PharmColors.primaryDark,
        surface: PharmColors.surfaceWhite,
        error: PharmColors.error,
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: PharmColors.surfaceWhite,
        elevation: 0,
        iconTheme: const IconThemeData(color: PharmColors.primary),
        titleTextStyle: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimaryLight),
      ),
      textTheme: TextTheme(
        displayLarge: PharmTextStyles.h1.copyWith(color: PharmColors.textPrimaryLight),
        displayMedium: PharmTextStyles.h2.copyWith(color: PharmColors.textPrimaryLight),
        displaySmall: PharmTextStyles.h3.copyWith(color: PharmColors.textPrimaryLight),
        headlineMedium: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimaryLight),
        bodyLarge: PharmTextStyles.bodyLarge.copyWith(color: PharmColors.textPrimaryLight),
        bodyMedium: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textPrimaryLight),
        bodySmall: PharmTextStyles.bodySmall.copyWith(color: PharmColors.textSecondaryLight),
        labelLarge: PharmTextStyles.button.copyWith(color: Colors.white),
        labelSmall: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiaryLight),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: PharmColors.primary,
          foregroundColor: Colors.white,
          textStyle: PharmTextStyles.button,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          elevation: 2,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: PharmColors.surfaceWhite,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.dividerLight),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.dividerLight),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: PharmColors.primary, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        labelStyle: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondaryLight),
        hintStyle: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textTertiaryLight),
      ),
      cardTheme: CardThemeData(
        color: PharmColors.surfaceWhite,
        elevation: 6,
        shadowColor: Colors.black.withOpacity(0.06),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: PharmColors.dividerLight, width: 0.5),
        ),
      ),
      scrollbarTheme: ScrollbarThemeData(
        thumbColor: WidgetStateProperty.all(PharmColors.primary.withOpacity(0.2)),
        trackColor: WidgetStateProperty.all(PharmColors.dividerLight.withOpacity(0.1)),
        thickness: WidgetStateProperty.all(6),
        radius: const Radius.circular(10),
        thumbVisibility: WidgetStateProperty.all(false),
        interactive: true,
      ),
    );
  }
}

extension PharmGlowExtension on Widget {
  Widget withGlow({Color? color, double blurRadius = 12, double spreadRadius = 2}) {
    return Container(
      decoration: BoxDecoration(
        boxShadow: [
          BoxShadow(
            color: color ?? PharmColors.primary.withOpacity(0.3),
            blurRadius: blurRadius,
            spreadRadius: spreadRadius,
          ),
        ],
      ),
      child: this,
    );
  }
}


