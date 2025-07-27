# Symfony PHP DevContainer

This devcontainer provides a fast, optimized development environment for Symfony PHP applications with PostgreSQL.

## Features

- **PHP 8.3** with all necessary extensions for Symfony
- **PostgreSQL 14** database
- **Adminer** for database management (port 8888)
- **Symfony CLI** for development tools
- **Composer** for dependency management
- **Node.js & npm** for frontend assets
- **Git** for version control
- **Optimized PHP settings** for development
- **OPcache** configured for performance

## Getting Started

1. **Prerequisites**: Make sure you have Docker and VS Code with the Remote - Containers extension installed.

2. **Open in DevContainer**:
   - Open the project in VS Code
   - Press `Ctrl+Shift+P` (or `Cmd+Shift+P` on Mac)
   - Select "Dev Containers: Reopen in Container"

3. **First Run**: The container will automatically:
   - Wait for the database to be ready
   - Install Composer dependencies
   - Clear Symfony cache
   - Run database migrations
   - Set proper permissions

**Note**: The first build may take a few minutes as it downloads and builds the container image.

## Services

- **App**: PHP Symfony application (port 8000)
- **Database**: PostgreSQL 14 (port 5432)
- **Adminer**: Database management interface (port 8888)

## Database Configuration

The database is pre-configured with:
- **Host**: `database`
- **Port**: `5432`
- **Database**: `gh-archive-keyword`
- **Username**: `yousign`
- **Password**: `123456789`

## Useful Commands

```bash
# Start Symfony development server
symfony server:start

# Run tests
php bin/phpunit

# Clear cache
php bin/console cache:clear

# Run migrations
php bin/console doctrine:migrations:migrate

# Create new migration
php bin/console make:migration

# Load fixtures
php bin/console doctrine:fixtures:load

# Check Symfony requirements
symfony check:requirements
```

## VS Code Extensions

The devcontainer automatically installs useful extensions:
- PHP Intelephense
- PHP Debug (Xdebug)
- PHP CS Fixer
- Symfony for VS Code
- Docker
- YAML
- JSON

## Performance Optimizations

- **OPcache** enabled for faster PHP execution
- **Composer cache** persisted between sessions
- **Database data** persisted in Docker volumes
- **File watching** optimized for large projects

## Troubleshooting

### Build Issues
If you encounter build errors:
1. Make sure Docker is running
2. Try rebuilding the container: `Ctrl+Shift+P` → "Dev Containers: Rebuild Container"
3. Check Docker logs: `docker logs <container-name>`

### Port Conflicts
If you get port conflicts, you can modify the ports in `.devcontainer/docker-compose.yml`.

### Database Connection Issues
Make sure the database service is running:
```bash
docker-compose ps
```

### Permission Issues
The container runs as a non-root user (vscode). If you encounter permission issues:
```bash
# Inside the container
sudo chown -R vscode:vscode /workspace
```

### Manual Setup
If automatic setup fails, you can run the setup manually:
```bash
# Inside the container
./.devcontainer/setup.sh
```

## Development Workflow

1. **Code**: Edit files in VS Code - changes are immediately reflected
2. **Test**: Run `php bin/phpunit` to execute tests
3. **Database**: Use Adminer at `http://localhost:8888` to manage the database
4. **Server**: Access your app at `http://localhost:8000`

## Environment Variables

You can customize the environment by creating a `.env.local` file in the project root with your specific configuration. 