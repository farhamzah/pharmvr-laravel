import 'package:flutter/material.dart';

enum DeviceType { mobile, tablet, desktop }

class ResponsiveHelper {
  static const double mobileBreakpoint = 768;
  static const double tabletBreakpoint = 1024;

  static DeviceType getDeviceType(BuildContext context) {
    double width = MediaQuery.of(context).size.width;
    if (width < mobileBreakpoint) {
      return DeviceType.mobile;
    } else if (width < tabletBreakpoint) {
      return DeviceType.tablet;
    } else {
      return DeviceType.desktop;
    }
  }

  static bool isMobile(BuildContext context) =>
      getDeviceType(context) == DeviceType.mobile;

  static bool isTablet(BuildContext context) =>
      getDeviceType(context) == DeviceType.tablet;

  static bool isDesktop(BuildContext context) =>
      getDeviceType(context) == DeviceType.desktop;

  static bool isMobileOrTablet(BuildContext context) =>
      MediaQuery.of(context).size.width < tabletBreakpoint;
}
