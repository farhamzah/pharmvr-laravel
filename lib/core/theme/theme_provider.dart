import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeNotifier extends Notifier<ThemeMode> {
  static const _themeKey = 'user_theme_mode';

  @override
  ThemeMode build() {
    // We can't do async build easily in build() without returning a Future,
    // so we'll initialize it synchronously as Dark and then load the real value.
    _loadTheme();
    return ThemeMode.dark;
  }

  Future<void> _loadTheme() async {
    final prefs = await SharedPreferences.getInstance();
    final themeStr = prefs.getString(_themeKey);
    
    if (themeStr != null) {
      state = _fromStr(themeStr);
    } else {
      // On first launch, explicitly save 'dark' and set state
      await prefs.setString(_themeKey, 'dark');
      state = ThemeMode.dark;
    }
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    state = mode;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_themeKey, _toStr(mode));
  }

  String _toStr(ThemeMode mode) {
    switch (mode) {
      case ThemeMode.light: return 'light';
      case ThemeMode.dark: return 'dark';
      default: return 'dark'; // Fallback to dark
    }
  }

  ThemeMode _fromStr(String str) {
    switch (str) {
      case 'light': return ThemeMode.light;
      case 'dark': return ThemeMode.dark;
      default: return ThemeMode.dark; // Absolute default
    }
  }
}

final themeProvider = NotifierProvider<ThemeNotifier, ThemeMode>(() {
  return ThemeNotifier();
});
