#!/bin/bash

# Setup script for Symfony devcontainer

echo "🚀 Setting up Symfony development environment..."

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
until pg_isready -h database -p 5432 -U yousign 2>/dev/null; do
  echo "Database is unavailable - sleeping"
  sleep 2
done
echo "✅ Database is ready!"

# Install Composer dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Clear Symfony cache
echo "🧹 Clearing Symfony cache..."
php bin/console cache:clear

# Run database migrations
echo "🗄️ Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Set proper permissions
echo "🔐 Setting proper permissions..."
chmod -R 777 var/cache var/log

# Install pre-commit
pip install pre-commit
pre-commit install

# Create cache directory for PHPStan
mkdir -p var/cache/dev

echo "✅ Setup complete! Your Symfony development environment is ready."
echo "🌐 Access your application at: http://localhost:8000"
echo "🗄️ Access Adminer at: http://localhost:8888" 