class DashboardData {
  final String userName;
  final int totalModules;
  final int completedModules;
  final int vrSessions;
  final double averageScore;
  final bool isVrHeadsetConnected;
  final ActiveModule? currentModule;
  /// 0 = pre-test, 1 = VR training, 2 = post-test, 3 = completed
  final int trainingStage;
  final int xpGained;
  final String? latestNewsTitle;
  final String? bannerImageUrl;

  const DashboardData({
    required this.userName,
    required this.totalModules,
    required this.completedModules,
    required this.vrSessions,
    required this.averageScore,
    required this.isVrHeadsetConnected,
    this.currentModule,
    this.trainingStage = 0,
    this.xpGained = 0,
    this.latestNewsTitle,
    this.bannerImageUrl,
  });

  factory DashboardData.mock() {
    return const DashboardData(
      userName: 'Alex',
      totalModules: 12,
      completedModules: 4,
      vrSessions: 8,
      averageScore: 92.5,
      isVrHeadsetConnected: true,
      trainingStage: 1,
      xpGained: 2450,
      latestNewsTitle: 'New CPOB 2024 guidelines released by BPOM',
      bannerImageUrl: 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=800&q=80',
      currentModule: ActiveModule(
        id: 'MOD-005',
        title: 'Cleanroom Gowning Protocol',
        description: 'Master the essential GMP standards for cleanroom entry, including gowning and sanitization.',
        progress: 0.3,
        requiresVr: true,
        hasPreTestAction: true,
      ),
    );
  }
}

class ActiveModule {
  final String id;
  final String title;
  final String description;
  final double progress;
  final bool requiresVr;
  final bool hasPreTestAction;

  const ActiveModule({
    required this.id,
    required this.title,
    required this.description,
    required this.progress,
    required this.requiresVr,
    required this.hasPreTestAction,
  });
}

