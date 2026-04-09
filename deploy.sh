#!/bin/bash
# ==========================================
# PharmVR Pro - VPS Deployment Script
# Run this inside the backend folder on the VPS
# ==========================================

set -e # Exit immediately if a command exits with a non-zero status

echo "🚀 Starting PharmVR Pro Backend Deployment..."

# 1. Enter Maintenance Mode
# echo "🔒 Turning on maintenance mode..."
# php artisan down --render="errors::503" --secret="bypass-token-here" || true

# 2. Pull Latest Code
echo "📥 Pulling latest code from Git..."
git gc --auto
# Resetting to origin/master to handle divergent branches after force push
git fetch origin
git reset --hard origin/master

# 3. Install/Update Composer Dependencies
echo "📦 Installing composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 4. Install/Update NPM Dependencies & Build Assets
echo "📦 Installing NPM dependencies..."
npm install --no-interaction

echo "🏗️ Building frontend assets (responsiveness)..."
npm run build

# 5. Clear old caches
echo "🧹 Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# 5. Run Database Migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 6. Rebuild Caches (Optimization)
echo "⚡ Optimizing application caches..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 7. Restoring Storage Link
echo "🔗 Ensuring storage links exist..."
php artisan storage:link || true

# 8. Restart Queues
echo "🔄 Restarting queue workers..."
php artisan queue:restart || true

# 9. Exit Maintenance Mode
# echo "🔓 Turning off maintenance mode..."
# php artisan up

# 10. Fix Permissions
echo "🔐 Fixing directory permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment completed successfully!"
