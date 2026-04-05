import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../config/network_constants.dart';
import '../theme/pharm_text_styles.dart';

class PharmNetworkAvatar extends StatelessWidget {
  final String? url;
  final String displayName;
  final double size;
  final double borderWidth;
  final Color? borderColor;

  const PharmNetworkAvatar({
    super.key,
    this.url,
    required this.displayName,
    this.size = 48.0,
    this.borderWidth = 1.5,
    this.borderColor,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final effectiveBorderColor = borderColor ?? theme.primaryColor.withOpacity(0.35);

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(color: effectiveBorderColor, width: borderWidth),
        boxShadow: [
          BoxShadow(
            color: theme.primaryColor.withOpacity(0.1),
            blurRadius: size * 0.2,
            spreadRadius: 1,
          )
        ],
      ),
      child: ClipOval(
        child: _buildImage(context),
      ),
    );
  }

  Widget _buildImage(BuildContext context) {
    if (url == null || url!.isEmpty) {
      return _buildFallback(context);
    }

    final sanitizedUrl = NetworkConstants.sanitizeUrl(url!);

    if (kIsWeb) {
      // Standard Image.network works better with CORS headers on Web
      return Image.network(
        sanitizedUrl,
        fit: BoxFit.cover,
        errorBuilder: (context, error, stackTrace) => _buildFallback(context),
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return _buildPlaceholder(context);
        },
      );
    }

    return CachedNetworkImage(
      imageUrl: sanitizedUrl,
      fit: BoxFit.cover,
      placeholder: (context, url) => _buildPlaceholder(context),
      errorWidget: (context, url, error) => _buildFallback(context),
    );
  }

  Widget _buildPlaceholder(BuildContext context) {
    return Container(
      color: Theme.of(context).colorScheme.surface,
      child: const Center(
        child: SizedBox(
          width: 16,
          height: 16,
          child: CircularProgressIndicator(strokeWidth: 2),
        ),
      ),
    );
  }

  Widget _buildFallback(BuildContext context) {
    final initials = displayName.isNotEmpty ? displayName[0].toUpperCase() : 'U';
    return Container(
      color: Theme.of(context).primaryColor.withOpacity(0.1),
      child: Center(
        child: Text(
          initials,
          style: PharmTextStyles.h3.copyWith(
            color: Theme.of(context).primaryColor,
            fontSize: size * 0.4,
          ),
        ),
      ),
    );
  }
}
