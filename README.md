# PodKeep

A Laravel-based podcast feed management application with React frontend.

## Features

- Podcast feed management
- Media file processing
- Library organization
- User authentication
- Modern React frontend with Inertia.js

## Quick Start

### Development

```bash
cp .env.example .env          # create env file (edit APP_PORT if needed)
docker compose up              # builds and starts dev environment
```

**Access at:** http://localhost:8000

**Common commands:**
```bash
docker compose logs -f app     # view logs
docker compose exec app sh     # shell access
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose down            # stop services
```

### Production Deployment

Build and deploy with a single VM behind Traefik:

```bash
# Build images
docker compose -f docker-compose.prod.yml build

# Deploy
docker compose -f docker-compose.prod.yml up -d
```

Set `APP_HOST` in your `.env` to configure the Traefik host rule (defaults to `podkeep.app`).

The production setup requires an external `traefik_web` network. Create it once:
```bash
docker network create traefik_web
```

## Docker Architecture

- **Dockerfile** — multi-stage build with `dev`, `app`, and `web` targets
- **docker-compose.yml** — development (source mounted, port 8000)
- **docker-compose.prod.yml** — production (baked images, Traefik, named volumes, worker)
- **.env** — shared variables (`APP_HOST`, `APP_PORT`)

| Aspect | Development | Production |
|--------|-------------|------------|
| Source Code | Mounted from `./src` | Baked into image |
| Access | http://localhost:8000 | Via Traefik/SSL |
| Volumes | Bind mounts | Named volumes |
| Worker | No | Yes (queue worker) |

## Prerequisites

- Docker and Docker Compose installed
- Port 8000 available (development)

## Local Development (Without Docker)

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

```bash
docker compose exec app php artisan test
```

## Troubleshooting

### Permission Issues (Development)

```bash
sudo chown -R 82:82 ./src/storage ./src/bootstrap/cache
chmod -R 755 ./src/storage ./src/bootstrap/cache
```

### Assets Not Loading

```bash
docker compose exec app sh -c "cd /var/www/html && npm run build"
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
├── docker-compose.yml            # Development
├── docker-compose.prod.yml       # Production
├── Dockerfile                    # Multi-stage container build
├── docker-entrypoint.sh          # Container entrypoint
├── .env.example                  # Environment variable template
└── README.md
```

## License

This project is open-sourced software licensed under the MIT license.
