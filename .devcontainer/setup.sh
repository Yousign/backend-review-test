#!/bin/bash

# Setup script for Symfony devcontainer

echo "🚀 Setting up Symfony development environment..."

# Fix Docker permissions first (critical for your issue)
echo "🐳 Setting up Docker permissions..."
if [ -S /var/run/docker.sock ]; then
    # Get the Docker socket group ID from the host
    DOCKER_SOCK_GID=$(stat -c '%g' /var/run/docker.sock)
    echo "Docker socket GID: $DOCKER_SOCK_GID"

    # Check if docker group exists and create/update it
    if ! getent group docker > /dev/null 2>&1; then
        echo "Creating docker group with GID $DOCKER_SOCK_GID"
        sudo groupadd -g $DOCKER_SOCK_GID docker
    else
        # Update existing docker group GID if different
        CURRENT_DOCKER_GID=$(getent group docker | cut -d: -f3)
        if [ "$CURRENT_DOCKER_GID" != "$DOCKER_SOCK_GID" ]; then
            echo "Updating docker group GID from $CURRENT_DOCKER_GID to $DOCKER_SOCK_GID"
            sudo groupmod -g $DOCKER_SOCK_GID docker
        fi
    fi

    # Add current user to docker group
    echo "Adding $(whoami) to docker group..."
    sudo usermod -aG docker $(whoami)

    # Change docker socket permissions as fallback
    sudo chmod 666 /var/run/docker.sock

    echo "✅ Docker permissions configured!"

    # Test Docker access
    if timeout 10 docker version > /dev/null 2>&1; then
        echo "✅ Docker is accessible!"
    else
        echo "⚠️  Docker access test failed. You may need to restart your terminal or container."
    fi
else
    echo "⚠️  Docker socket not found. Docker-in-Docker should handle this."
fi

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
else
    echo "📦 Composer dependencies already installed, checking for updates..."
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
echo "🔧 Installing pre-commit..."
pip install pre-commit
pre-commit install

# Create cache directory for PHPStan
mkdir -p var/cache/dev

# Set up useful aliases
echo "🔧 Setting up helpful aliases..."
cat >> ~/.bashrc << 'EOF'

# Symfony aliases
alias sf='php bin/console'
alias cc='php bin/console cache:clear'
alias cw='php bin/console cache:warmup'
alias mm='php bin/console doctrine:migrations:migrate'
alias fixtures='php bin/console doctrine:fixtures:load --no-interaction'

# Docker aliases
alias dc='docker-compose'
alias dcu='docker-compose up -d'
alias dcd='docker-compose down'
alias dcr='docker-compose restart'
alias dps='docker ps'
alias dlog='docker-compose logs -f'

# Project specific
alias start='make start'
alias stop='make stop'
alias shell='make shell'
alias test='make unit-test'

EOF

# Apply group changes for current session
echo "🔄 Applying group changes..."
newgrp docker << EOFGROUP
echo "Group changes applied for current session"
EOFGROUP

echo "✅ Setup complete! Your Symfony development environment is ready."
echo ""
echo "🌐 Services available:"
echo "  • Symfony App: http://localhost:8000"
echo "  • Adminer:     http://localhost:8888"
echo ""
