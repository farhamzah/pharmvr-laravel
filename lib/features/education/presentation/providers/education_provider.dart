import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_provider.dart';
import '../../data/repositories/education_repository.dart';
import '../../domain/models/learning_module.dart';

final educationRepositoryProvider = Provider<EducationRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return EducationRepository(dio);
});

class EdukasiNotifier extends AsyncNotifier<List<LearningModule>> {
  @override
  Future<List<LearningModule>> build() async {
    final repository = ref.watch(educationRepositoryProvider);
    return repository.getModules();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      final repository = ref.read(educationRepositoryProvider);
      return repository.getModules();
    });
  }

  List<LearningModule> getItemsByType(String type) {
    return state.maybeWhen(
      data: (items) => items.where((item) => item.type == type).toList(),
      orElse: () => [],
    );
  }
}

final edukasiProvider = AsyncNotifierProvider<EdukasiNotifier, List<LearningModule>>(() {
  return EdukasiNotifier();
});

final moduleDetailProvider = FutureProvider.family<EducationDetail, String>((ref, slug) async {
  final repository = ref.watch(educationRepositoryProvider);
  return repository.getModuleDetail(slug);
});
