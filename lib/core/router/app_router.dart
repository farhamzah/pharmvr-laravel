import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:go_router/go_router.dart';

// Shell & Screens
import '../../features/dashboard/presentation/screens/main_shell_screen.dart';
import '../../features/dashboard/presentation/screens/dashboard_screen.dart';
import '../../features/auth/presentation/screens/splash_screen.dart';
import '../../features/auth/presentation/screens/login_screen.dart';
import '../../features/auth/presentation/screens/register_screen.dart';
import '../../features/auth/presentation/screens/forgot_password_screen.dart';
import '../../features/landing/presentation/screens/landing_screen.dart';
import '../../features/news/presentation/screens/news_screen.dart';
import '../../features/news/presentation/screens/news_detail_screen.dart';
import '../../features/news/presentation/screens/news_external_detail_screen.dart';
import '../../features/education/presentation/screens/education_screen.dart';
import '../../features/education/presentation/screens/education_detail_screen.dart';
import '../../features/education/presentation/screens/video_player_screen.dart';
import '../../features/education/presentation/screens/document_viewer_screen.dart';
import '../../features/ai_assistant/presentation/screens/ai_assistant_screen.dart';
import '../../features/ai_assistant/presentation/screens/ai_chat_session_screen.dart';
import '../../features/ai_assistant/presentation/screens/chat_history_screen.dart';
import '../../features/profile/presentation/screens/profile_screen.dart';
import '../../features/profile/presentation/screens/edit_profile_screen.dart';
import '../../features/profile/presentation/screens/change_password_screen.dart';
import '../../features/profile/presentation/screens/notifications_screen.dart';
import '../../features/profile/presentation/screens/language_screen.dart';
import '../../features/profile/presentation/screens/appearance_screen.dart';
import 'package:pharmvrpro/features/profile/presentation/screens/about_screen.dart';
import '../../features/profile/presentation/screens/help_center_screen.dart';
import '../../features/profile/presentation/screens/legal_content_screen.dart';

// VR Screens
import '../../features/vr_experience/presentation/screens/vr_launch_screen.dart';
import '../../features/vr_experience/presentation/screens/vr_connect_screen.dart';
import '../../features/vr_experience/presentation/screens/vr_summary_screen.dart';
import '../../features/vr_experience/presentation/screens/training_progress_screen.dart';

// Assessment Screens
import '../../features/assessment/presentation/screens/assessment_intro_screen.dart';
import '../../features/assessment/presentation/screens/assessment_question_screen.dart';
import '../../features/assessment/presentation/screens/assessment_review_screen.dart';
import '../../features/assessment/presentation/screens/assessment_result_screen.dart';

final GlobalKey<NavigatorState> rootNavigatorKey = GlobalKey<NavigatorState>();
final GlobalKey<NavigatorState> shellNavigatorNewsKey = GlobalKey<NavigatorState>(debugLabel: 'news');
final GlobalKey<NavigatorState> shellNavigatorEduKey = GlobalKey<NavigatorState>(debugLabel: 'edu');
final GlobalKey<NavigatorState> shellNavigatorHubKey = GlobalKey<NavigatorState>(debugLabel: 'hub');
final GlobalKey<NavigatorState> shellNavigatorAiKey = GlobalKey<NavigatorState>(debugLabel: 'ai');
final GlobalKey<NavigatorState> shellNavigatorProfileKey = GlobalKey<NavigatorState>(debugLabel: 'profile');

class AppRouter {
  static final GoRouter router = GoRouter(
    navigatorKey: rootNavigatorKey,
    initialLocation: kIsWeb ? '/' : '/splash',
    routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => const LandingScreen(),
    ),
    GoRoute(
      path: '/splash',
      builder: (context, state) => const SplashScreen(),
    ),
    GoRoute(
      path: '/auth/login',
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: '/auth/register',
      builder: (context, state) => const RegisterScreen(),
    ),
    GoRoute(
      path: '/auth/forgot-password',
      builder: (context, state) => const ForgotPasswordScreen(),
    ),

    // --- Assessment Routes ---
    GoRoute(
      path: '/assessment/intro/:moduleId/:type',
      pageBuilder: (context, state) => _fadeSlide(
        state,
        AssessmentIntroScreen(
          moduleId: state.pathParameters['moduleId']!,
          type: state.pathParameters['type']!,
        ),
      ),
    ),
    GoRoute(
      path: '/assessment/question/:moduleId/:type',
      pageBuilder: (context, state) => _fadeSlide(
        state,
        AssessmentQuestionScreen(
          moduleId: state.pathParameters['moduleId']!,
          type: state.pathParameters['type']!,
        ),
      ),
    ),
    GoRoute(
      path: '/assessment/review/:moduleId/:type',
      pageBuilder: (context, state) => _fadeSlide(
        state,
        AssessmentReviewScreen(
          moduleId: state.pathParameters['moduleId']!,
          type: state.pathParameters['type']!,
        ),
      ),
    ),
    GoRoute(
      path: '/assessment/result/:moduleId/:type',
      pageBuilder: (context, state) => _fadeSlide(
        state,
        AssessmentResultScreen(
          moduleId: state.pathParameters['moduleId']!,
          type: state.pathParameters['type']!,
        ),
      ),
    ),

    // --- VR Routes ---
    GoRoute(
      path: '/vr/launch',
      pageBuilder: (context, state) => _fadeSlide(state, const VrLaunchScreen()),
    ),
    GoRoute(
      path: '/vr/progress',
      pageBuilder: (context, state) => _fadeSlide(state, const TrainingProgressScreen()),
    ),
    GoRoute(
      path: '/vr/connect',
      pageBuilder: (context, state) => _fadeSlide(state, const VrConnectScreen()),
    ),
    GoRoute(
      path: '/vr/summary',
      builder: (context, state) => const VrSummaryScreen(),
    ),

    // --- Main Shell ---
    StatefulShellRoute.indexedStack(
      builder: (context, state, navigationShell) {
        return MainShellScreen(navigationShell: navigationShell);
      },
      branches: [
        // 0: Home (Dashboard)
        StatefulShellBranch(
          navigatorKey: shellNavigatorHubKey,
          routes: [
            GoRoute(
              path: '/dashboard',
              builder: (context, state) => const DashboardScreen(),
            ),
          ],
        ),
        // 1: Edukasi
        StatefulShellBranch(
          navigatorKey: shellNavigatorEduKey,
          routes: [
            GoRoute(
              path: '/education',
              builder: (context, state) => const EducationScreen(),
              routes: [
                GoRoute(
                  path: 'detail/:contentId',
                  pageBuilder: (context, state) => _fadeSlide(
                    state,
                    EducationDetailScreen(
                      contentId: state.pathParameters['contentId']!,
                    ),
                  ),
                  routes: [
                    GoRoute(
                      path: 'player/:videoId',
                      builder: (context, state) {
                        final videoId = state.pathParameters['videoId']!;
                        final title = state.uri.queryParameters['title'] ?? 'Video Materi';
                        return VideoPlayerScreen(
                          videoId: videoId,
                          title: title,
                        );
                      },
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
        // 2: PharmAI
        StatefulShellBranch(
          navigatorKey: shellNavigatorAiKey,
          routes: [
            GoRoute(
              path: '/ai-assistant',
              builder: (context, state) => const AiAssistantScreen(),
              routes: [
                GoRoute(
                  path: 'history',
                  builder: (context, state) => const ChatHistoryScreen(),
                ),
                GoRoute(
                  path: 'chat/:id',
                  builder: (context, state) {
                    final id = state.pathParameters['id'];
                    final prompt = state.uri.queryParameters['prompt'];
                    final mode = state.uri.queryParameters['mode'];
                    return AiChatSessionScreen(
                      sessionId: id == 'new' ? null : id,
                      initialPrompt: id == 'new' ? prompt : null,
                      assistantMode: mode,
                    );
                  },
                ),
              ],
            ),
          ],
        ),
        // 3: News
        StatefulShellBranch(
          navigatorKey: shellNavigatorNewsKey,
          routes: [
            GoRoute(
              path: '/news',
              builder: (context, state) => const NewsScreen(),
              routes: [
                GoRoute(
                  path: 'detail/:slug',
                  builder: (context, state) {
                    final slug = state.pathParameters['slug']!;
                    return NewsDetailScreen(
                      articleId: slug,
                    );
                  },
                ),
                GoRoute(
                  path: 'external/:slug',
                  builder: (context, state) {
                    final slug = state.pathParameters['slug']!;
                    return NewsExternalDetailScreen(
                      articleId: slug,
                    );
                  },
                ),
              ],
            ),
          ],
        ),
        // 4: Profile
        StatefulShellBranch(
          navigatorKey: shellNavigatorProfileKey,
          routes: [
            GoRoute(
              path: '/profile',
              builder: (context, state) => const ProfileScreen(),
              routes: [
                GoRoute(
                  path: 'edit',
                  builder: (context, state) => const EditProfileScreen(),
                ),
                GoRoute(
                  path: 'change-password',
                  builder: (context, state) => const ChangePasswordScreen(),
                ),
              ],
            ),
            GoRoute(
              path: '/settings',
              redirect: (context, state) {
                if (state.uri.path == '/settings') return '/profile';
                return null;
              },
              routes: [
                GoRoute(
                  path: 'notifications',
                  builder: (context, state) => const NotificationsScreen(),
                ),
                GoRoute(
                  path: 'language',
                  builder: (context, state) => const LanguageScreen(),
                ),
                GoRoute(
                  path: 'appearance',
                  builder: (context, state) => const AppearanceScreen(),
                ),
              ],
            ),
            GoRoute(
              path: '/support',
              redirect: (context, state) {
                if (state.uri.path == '/support') return '/profile';
                return null;
              },
              routes: [
                GoRoute(
                  path: 'help-center',
                  builder: (context, state) => const HelpCenterScreen(),
                ),
                GoRoute(
                  path: 'about',
                  builder: (context, state) => const AboutPharmVrScreenNew(),
                ),
                GoRoute(
                  path: 'privacy-policy',
                  builder: (context, state) => const LegalContentScreen(type: LegalContentType.privacy),
                ),
                GoRoute(
                  path: 'terms-of-service',
                  builder: (context, state) => const LegalContentScreen(type: LegalContentType.terms),
                ),
                GoRoute(
                  path: 'website-info',
                  builder: (context, state) => const LegalContentScreen(type: LegalContentType.website),
                ),
              ],
            ),
          ],
        ),
      ],
    ),
  ],
);
}

/// Premium fade + slide-up page transition for detail/overlay screens.
CustomTransitionPage<void> _fadeSlide(GoRouterState state, Widget child) {
  return CustomTransitionPage<void>(
    key: state.pageKey,
    child: child,
    transitionDuration: const Duration(milliseconds: 350),
    reverseTransitionDuration: const Duration(milliseconds: 250),
    transitionsBuilder: (context, animation, secondaryAnimation, child) {
      final fadeAnim = CurvedAnimation(parent: animation, curve: Curves.easeOut);
      final slideAnim = Tween<Offset>(
        begin: const Offset(0, 0.04),
        end: Offset.zero,
      ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic));

      return FadeTransition(
        opacity: fadeAnim,
        child: SlideTransition(
          position: slideAnim,
          child: child,
        ),
      );
    },
  );
}
