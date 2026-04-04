import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

import '../../domain/models/vr_connection_state.dart';
import '../widgets/vr_connection_hero.dart';
import '../widgets/vr_status_card.dart';
import '../widgets/vr_instructions_block.dart';
import '../widgets/vr_connection_actions.dart';

class VrConnectionScreen extends StatefulWidget {
  const VrConnectionScreen({super.key});

  @override
  State<VrConnectionScreen> createState() => _VrConnectionScreenState();
}

class _VrConnectionScreenState extends State<VrConnectionScreen> {
  // Current session state mimicking Riverpod setup
  VrDeviceSession _session = const VrDeviceSession.initial();

  // Fake Network Calls for Simulation
  Future<void> _startPairing() async {
    setState(() {
      _session = const VrDeviceSession(
        status: VrConnectionStatus.pairing,
      );
    });

    // Simulate 3 seconds of network searching
    await Future.delayed(const Duration(seconds: 3));

    // For demo purposes, we randomly fail 20% of the time, succeed 80%
    final bool success = DateTime.now().millisecond % 5 != 0;

    if (mounted) {
      if (success) {
        setState(() {
          _session = VrDeviceSession(
             deviceId: 'VR-REQ-894',
             deviceName: 'Oculus Quest 3 (Lab 1)',
             lastConnected: DateTime.now(),
             status: VrConnectionStatus.connected,
          );
        });
      } else {
         setState(() {
          _session = const VrDeviceSession(
             errorMessage: 'Host VR tidak merespons. Pastikan di jaringan WiFi yang sama.',
             status: VrConnectionStatus.failed,
          );
        });
      }
    }
  }

  void _cancelPairing() {
    setState(() {
      _session = const VrDeviceSession.initial();
    });
  }

  void _continueToVrExperience() {
    // Navigate deep into the VR tracking dashboard
    // context.go('/vr/dashboard');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text(
          'Koneksi VR', // "VR Connection"
          style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color),
        ),
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        elevation: 0,
        centerTitle: true,
        iconTheme: IconThemeData(color: Theme.of(context).textTheme.displaySmall?.color),
        actions: [
          IconButton(
            icon: const Icon(Icons.info_outline_rounded),
            tooltip: 'Panduan Pairing', // "Pairing Guide"
            onPressed: () {},
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: LayoutBuilder(
        builder: (context, constraints) {
          return SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                minHeight: constraints.maxHeight, // Ensure column can stretch full height
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    children: [
                      VrConnectionHero(
                        status: _session.status,
                      ),
                      
                      const SizedBox(height: PharmSpacing.lg),
                      
                      VrStatusCard(
                        session: _session,
                      ),
                      
                      VrInstructionsBlock(
                         status: _session.status,
                      ),
                    ],
                  ),
                  
                  // Pushed to the bottom
                  VrConnectionActions(
                    status: _session.status,
                    onStartPairing: _startPairing,
                    onCancelPairing: _cancelPairing,
                    onRetry: _startPairing,
                    onContinue: _continueToVrExperience,
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
