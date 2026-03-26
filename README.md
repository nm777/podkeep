# Personal Podcasts

A Laravel-based podcast feed management application with React frontend.

## Features

- Podcast feed management
- Media file processing
- Library organization
- User authentication
- Modern React frontend with Inertia.js

## Quick Start

### Development

The fastest way to start development:

```bash
./dev.sh
```

This script:
- Creates `.env` file if needed
- Sets up proper permissions
- Builds and starts containers
- Runs migrations
- Optimizes the application

**Access at:** http://localhost:8000

### Development Commands

```bash
./dev.sh              # Start development environment
./dev.sh logs         # View logs
./dev.sh down         # Stop services
./dev.sh shell        # Access container shell
./dev.sh migrate      # Run migrations
./dev.sh test         # Run tests
./dev.sh restart      # Restart containers
```

### Production Deployment

Build and deploy to production:

```bash
# Build the production image
./docker-compose-wrapper.sh prod build

# Deploy
./docker-compose-wrapper.sh prod up -d

# Run migrations
./docker-compose-wrapper.sh prod exec app php artisan migrate --force

# View logs
./docker-compose-wrapper.sh prod logs -f
```

See [DOCKER_COMPOSE.md](DOCKER_COMPOSE.md) for detailed production deployment options.

## Docker Architecture

This project uses a modular Docker Compose structure:

- **docker-compose.base.yml** - Common configuration
- **docker-compose.dev.yml** - Development overrides (source mounts, port 8000)
- **docker-compose.prod.yml** - Production overrides (Traefik, named volumes, SSL)
- **dev.sh** - Development startup script
- **docker-compose-wrapper.sh** - Generic Docker Compose wrapper

### Development vs Production

| Aspect | Development | Production |
|--------|-------------|------------|
| Source Code | Mounted from `./src` | Baked into image |
| Access | http://localhost:8000 | Via Traefik/SSL |
| Volumes | Bind mounts | Named volumes |
| File Watching | Live reload | Static |

See [DOCKER_COMPOSE.md](DOCKER_COMPOSE.md) for complete documentation.

## Container Security

This application runs containers as a non-root user (`www-data`, UID 82) for improved security.

**Development:** Permissions are automatically handled by `./dev.sh`

**Production:** Code is baked into the image with correct permissions. No host permissions needed.

## Prerequisites

- Docker and Docker Compose installed
- Port 8000 available (development)

## Manual Docker Commands

If you prefer not to use the convenience scripts:

```bash
# Development
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml up -d --build
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml logs -f
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml down

# Production
docker-compose -f docker-compose.base.yml -f docker-compose.prod.yml build
docker-compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
docker-compose -f docker-compose.base.yml -f docker-compose.prod.yml down
```

## Local Development (Without Docker)

If you prefer to develop locally without Docker:

1. Install PHP 8.4+, Node.js, and Composer
2. Navigate to the `src/` directory
3. Install dependencies:
   ```bash
   composer install
   npm install
   ```
4. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Start development servers:
   ```bash
   composer run dev
   ```

## Testing

Run tests using the development script:
```bash
./dev.sh test
```

Or manually:
```bash
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml exec app php artisan test
```

## Troubleshooting

### Permission Issues (Development)

If you see permission errors:
```bash
sudo chown -R 82:82 ./src/storage ./src/bootstrap/cache
chmod -R 755 ./src/storage ./src/bootstrap/cache
```

### Container Won't Start

Check container logs:
```bash
./dev.sh logs
```

Common issues:
- Missing `src/.env` file (auto-created by `./dev.sh`)
- Port 8000 already in use
- Docker daemon not running

### Assets Not Loading

If frontend assets aren't loading:
```bash
./dev.sh shell
npm run build
exit
```

## File Structure

```
.
├── src/                          # Application code
│   ├── app/                      # Laravel application
│   ├── public/                   # Public assets
│   ├── storage/                  # Application storage
│   └── ...
├── docker/
│   └── nginx/
│       └── default.conf          # Nginx configuration
├── docker-compose.base.yml       # Base Docker configuration
├── docker-compose.dev.yml        # Development overrides
├── docker-compose.prod.yml       # Production overrides
├── dev.sh                        # Development startup script
├── docker-compose-wrapper.sh     # Docker Compose wrapper
├── Dockerfile                    # Container build definition
├── docker-entrypoint.sh          # Container entrypoint
└── README.md                     # This file
```

## License

This project is open-sourced software licensed under the MIT license.
