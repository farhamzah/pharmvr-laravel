import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_provider.dart';
import '../../data/repositories/app_setting_repository.dart';
import '../../domain/models/app_setting.dart';

final appSettingRepositoryProvider = Provider<AppSettingRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return AppSettingRepository(dio);
});

class AppSettingNotifier extends AsyncNotifier<AppSetting> {
  @override
  Future<AppSetting> build() async {
    final repository = ref.watch(appSettingRepositoryProvider);
    return repository.getSettings();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      final repository = ref.read(appSettingRepositoryProvider);
      return repository.getSettings();
    });
  }
}

final appSettingProvider = AsyncNotifierProvider<AppSettingNotifier, AppSetting>(() {
  return AppSettingNotifier();
});
