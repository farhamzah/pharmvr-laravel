import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_provider.dart';
import '../../data/repositories/home_repository.dart';
import '../../domain/models/home_hub.dart';

final homeRepositoryProvider = Provider<HomeRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return HomeRepository(dio);
});

class DashboardNotifier extends AsyncNotifier<HomeHubData> {
  @override
  Future<HomeHubData> build() async {
    final repository = ref.watch(homeRepositoryProvider);
    return repository.getHomeData();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      final repository = ref.read(homeRepositoryProvider);
      return repository.getHomeData();
    });
  }
}

final dashboardProvider = AsyncNotifierProvider<DashboardNotifier, HomeHubData>(() {
  return DashboardNotifier();
});
