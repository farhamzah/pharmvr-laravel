import 'package:flutter/material.dart';

class PharmColors {
  // --- Primary ---
  static const Color primary = Color(0xFF00E5FF);       // Cyan — hero accents, CTAs
  static const Color primaryDark = Color(0xFF00B8D4);    // Darker cyan — gradient stop
  static const Color primaryLight = Color(0xFF80F0FF);   // Soft cyan — hover tints, badges

  // --- Surfaces (Dark) ---
  static const Color background = Color(0xFF0A0F14);     // Deep dark slate
  static const Color surface = Color(0xFF151E27);        // Card base
  static const Color surfaceLight = Color(0xFF1C2733);   // Elevated card / modal
  static const Color divider = Color(0xFF2A3545);        // Borders, separators
  static const Color cardBorder = Color(0x1AFFFFFF);     // 10% white — subtle card edges

  // --- Surfaces (Light) ---
  static const Color backgroundLight = Color(0xFFF5F7FA);
  static const Color surfaceWhite = Color(0xFFFFFFFF);
  static const Color dividerLight = Color(0xFFE0E6ED);
  static const Color cardBorderLight = Color(0xFFD1D9E6);

  // --- Semantic ---
  static const Color error = Color(0xFFCF6679);          // Subdued red
  static const Color success = Color(0xFF00E676);        // Neon green
  static const Color warning = Color(0xFFFFB74D);        // Warm amber
  static const Color info = Color(0xFF64B5F6);           // Calm blue

  // --- Text (Dark) ---
  static const Color textPrimary = Color(0xFFFFFFFF);
  static const Color textSecondary = Color(0xFFB0BEC5);
  static const Color textTertiary = Color(0xFF78909C);   // Captions, hints

  // --- Text (Light) ---
  static const Color textPrimaryLight = Color(0xFF1A1C1E);
  static const Color textSecondaryLight = Color(0xFF494B4E);
  static const Color textTertiaryLight = Color(0xFF74777F);

  // --- Effects ---
  static const Color accentGlow = Color(0x3300E5FF);     // CTA shadow glow
}

