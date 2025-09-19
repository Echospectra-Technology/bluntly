#!/bin/bash

# Bluntly Production Deployment Script

set -e

echo "🚀 Starting deployment..."

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Clear and cache config
echo "⚙️ Optimizing configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
echo "🛣️ Optimizing routes..."
php artisan route:clear
php artisan route:cache

# Clear and cache views
echo "👀 Optimizing views..."
php artisan view:clear
php artisan view:cache

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear application cache
echo "🧹 Clearing application cache..."
php artisan cache:clear

# Optimize autoloader
echo "🔧 Optimizing autoloader..."
composer dump-autoload --optimize

# Build assets
echo "🎨 Building production assets..."
npm run build

# Set proper permissions
echo "🔐 Setting file permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart services
echo "🔄 Restarting services..."
sudo systemctl reload nginx
sudo systemctl restart php8.3-fpm

# Warm up cache
echo "🔥 Warming up cache..."
php artisan cache:warm || echo "Cache warming not implemented yet"

echo "✅ Deployment completed successfully!"

# Run health check
echo "🏥 Running health check..."
curl -f http://localhost/health || echo "Health check failed - please verify manually"

echo "🎉 Deployment finished!"