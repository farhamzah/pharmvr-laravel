# PharmVR Web Build Script
Write-Host "Starting Web Build..."

# Clean and Build
flutter clean
flutter pub get
Write-Host "Building Flutter Web..."
flutter build web --release --base-href "/"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Build failed."
    exit $LASTEXITCODE
}

# Compress
Write-Host "Compressing build artifacts..."
if (Test-Path "build/web_dist.tar.gz") { Remove-Item "build/web_dist.tar.gz" }
Set-Location build/web
tar -czf ../web_dist.tar.gz .
Set-Location ../..

Write-Host "Build Complete! Ready for manual upload."
Write-Host "Step: Run 'scp build/web_dist.tar.gz root@202.10.42.65:/tmp/'"
