import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/services.dart';
import 'core/router/app_router.dart';
import 'core/theme/pharm_theme.dart';
import 'core/theme/theme_provider.dart';
import 'core/localization/locale_provider.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:pharmvrpro/l10n/app_localizations.dart';

void main() {
  debugPrint('DEBUG: main() started');
  WidgetsFlutterBinding.ensureInitialized();
  debugPrint('DEBUG: WidgetsFlutterBinding initialized');
  // Enforce dark style for status bar initially
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
    statusBarColor: Colors.transparent,
    statusBarIconBrightness: Brightness.light,
    systemNavigationBarColor: Colors.transparent,
    systemNavigationBarIconBrightness: Brightness.light,
  ));
  debugPrint('DEBUG: SystemUIOverlayStyle set');
  runApp(const ProviderScope(child: PharmVrApp()));
  debugPrint('DEBUG: runApp executed');
}

class PharmVrApp extends ConsumerWidget {
  const PharmVrApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final themeMode = ref.watch(themeProvider);
    final locale = ref.watch(localeProvider);

    return MaterialApp.router(
      title: 'PharmVR',
      theme: PharmTheme.lightTheme,
      darkTheme: PharmTheme.darkTheme,
      themeMode: themeMode,
      locale: locale,
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: AppLocalizations.supportedLocales,
      routerConfig: goRouter,
      debugShowCheckedModeBanner: false,
    );
  }
}
