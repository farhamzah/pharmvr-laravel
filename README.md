# PharmVR Pro - Backend API ⚙️

This is the central API and administration layer for PharmVR Pro, built with Laravel 12. It serves the Flutter mobile application, the VR headsets, and powers the web-based admin dashboard.

## Requirements
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM (for compiling Vite assets)

## Development Setup

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
   - Update `DB_*` keys with your MySQL credentials.
   - Set `GEMINI_API_KEY` to enable the PharmAI assistant features.

3. **Initialize App & Storage**
   ```bash
   php artisan key:generate
   php artisan storage:link
   ```

4. **Database Setup**
   Run migrations and seed the database with required standard data:
   ```bash
   php artisan migrate --seed
   ```
   *(Note: The seeders create default testing accounts. Review `UserSeeder.php` and `AdminUserSeeder.php` for credentials).*

5. **Run Development Server**
   ```bash
   # Run Vite for frontend assets (Admin Panel)
   npm run dev
   # Run Laravel server
   php artisan serve
   ```

## Production Deployment (VPS Server)

This backend is designed to run securely on an Nginx + PHP-FPM stack on Debian/Ubuntu.

1. Clone/Transfer the code to your web directory (e.g., `/var/www/pharmvr-laravel`).
2. Copy the production template: `cp .env.production.example .env`.
3. Update `.env` with strong passwords, database info, and `APP_DEBUG=false`.
4. Configure your Nginx server block using the provided `nginx.conf.example`.
5. Make the deploy script executable and run it:
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```
   *The `deploy.sh` script automates Git pulling, composer optimization, migrations, and cache clearing.*
