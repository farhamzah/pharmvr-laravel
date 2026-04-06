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

Write-Host "Build Complete! Syncing to VPS..."

# Step 4: Upload to VPS
scp build/web_dist.tar.gz root@202.10.42.65:/tmp/

# Step 5: Extract on VPS
ssh root@202.10.42.65 "rm -rf /var/www/pharmvr-frontend/* && tar -xzf /tmp/web_dist.tar.gz -C /var/www/pharmvr-frontend/ && rm /tmp/web_dist.tar.gz"

Write-Host "Deployment Complete! 🚀"
Write-Host "Website: https://pharmvr.cloud"
