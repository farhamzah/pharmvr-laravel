import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:shimmer/shimmer.dart';
import '../config/network_constants.dart';
import '../theme/pharm_colors.dart';

class PharmNetworkImage extends StatelessWidget {
  final String url;
  final BoxFit fit;
  final double? width;
  final double? height;
  final BorderRadius? borderRadius;
  final Widget? errorWidget;

  const PharmNetworkImage({
    super.key,
    required this.url,
    this.fit = BoxFit.cover,
    this.width,
    this.height,
    this.borderRadius,
    this.errorWidget,
  });

  @override
  Widget build(BuildContext context) {
    if (url.isEmpty) return _buildError();

    // Ensure the URL is absolute for the emulator/device
    String finalUrl = url;
    if (!url.startsWith('http')) {
      // If relative, prepend the base storage URL (assumed to be root for now)
      // Actually, sanitizeUrl should have handled it if it came from backend as localhost
      // but if it's just 'storage/news/abc.png', we need to help it.
      final baseUrl = NetworkConstants.baseUrl.replaceAll('/api/v1', '');
      finalUrl = '$baseUrl/${url.startsWith('/') ? url.substring(1) : url}';
    }

    if (kDebugMode && finalUrl.contains('10.0.2.2')) {
      debugPrint('PharmNetworkImage Loading: $finalUrl');
    }

    Widget image = CachedNetworkImage(
      imageUrl: finalUrl,
      width: width,
      height: height,
      fit: fit,
      placeholder: (context, url) => _buildPlaceholder(),
      errorWidget: (context, url, error) {
        if (kDebugMode) {
          debugPrint('PharmNetworkImage Error [$url]: $error');
        }
        return errorWidget ?? _buildError();
      },
      // Add memory cache optimization
      memCacheWidth: 800, 
      maxWidthDiskCache: 1200,
    );

    if (borderRadius != null) {
      return ClipRRect(
        borderRadius: borderRadius!,
        child: image,
      );
    }

    return image;
  }

  Widget _buildPlaceholder() {
    return Shimmer.fromColors(
      baseColor: PharmColors.surfaceLight,
      highlightColor: PharmColors.surfaceLight.withOpacity(0.5),
      child: Container(
        width: width,
        height: height,
        color: PharmColors.surfaceLight,
      ),
    );
  }

  Widget _buildError() {
    return Container(
      width: width,
      height: height,
      color: PharmColors.surfaceLight,
      child: const Icon(
        Icons.image_not_supported_outlined,
        color: PharmColors.textTertiary,
      ),
    );
  }
}
