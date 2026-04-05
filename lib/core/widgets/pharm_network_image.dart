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

    // Use centralized sanitization logic
    final String finalUrl = NetworkConstants.sanitizeUrl(url);

    if (kDebugMode && finalUrl.contains('localhost')) {
      debugPrint('PharmNetworkImage Loading: $finalUrl');
    }

    Widget image;
    if (kIsWeb) {
      image = Image.network(
        finalUrl,
        width: width,
        height: height,
        fit: fit,
        errorBuilder: (context, error, stackTrace) {
          if (kDebugMode) debugPrint('PharmNetworkImage Web Error [$finalUrl]: $error');
          return errorWidget ?? _buildError();
        },
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return _buildPlaceholder();
        },
      );
    } else {
      image = CachedNetworkImage(
        imageUrl: finalUrl,
        width: width,
        height: height,
        fit: fit,
        placeholder: (context, url) => _buildPlaceholder(),
        errorWidget: (context, url, error) {
          if (kDebugMode) debugPrint('PharmNetworkImage Error [$url]: $error');
          return errorWidget ?? _buildError();
        },
        memCacheWidth: 800,
        maxWidthDiskCache: 1200,
      );
    }

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
