# Docker Compose Structure

This project uses a modular Docker Compose structure to separate base configuration from environment-specific overrides.

## File Structure

- **docker-compose.base.yml** - Common configuration shared by all environments
- **docker-compose.dev.yml** - Development-specific overrides (source mounts, exposed ports)
- **docker-compose.prod.yml** - Production-specific overrides (Traefik, named volumes, SSL)
- **docker-compose-wrapper.sh** - Convenience script for running commands

## Usage

### Using the Wrapper Script (Recommended)

```bash
# Development
./docker-compose-wrapper.sh dev up -d          # Start development
./docker-compose-wrapper.sh dev logs -f        # View logs
./docker-compose-wrapper.sh dev down           # Stop

# Production
./docker-compose-wrapper.sh prod build         # Build image
./docker-compose-wrapper.sh prod up -d         # Deploy
./docker-compose-wrapper.sh prod down          # Stop
```

### Manual Docker Compose Commands

```bash
# Development
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml up -d
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml logs -f
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml down

# Production
docker-compose -f docker-compose.base.yml -f docker-compose.prod.yml build
docker-compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
docker-compose -f docker-compose.base.yml -f docker-compose.prod.yml down
```

## Key Differences

### Development
- Source code mounted from `./src` for live editing
- Direct port access on `localhost:8000`
- No SSL/TLS
- Simpler networking

### Production
- Code baked into Docker image
- Traefik handles routing and SSL
- Named volumes for persistence
- Read-only mounts where possible
- More secure configuration

## Migration from Old Files

The old single-file configurations (`docker-compose.yml` and `docker-compose-deploy.yml`) are still available and work identically to before. You can:

1. **Keep using old files** - Nothing changes
2. **Switch to new modular structure** - More maintainable, follows best practices
3. **Gradual migration** - Use both approaches during transition

## Benefits of Modular Structure

- **DRY principle** - Common config defined once
- **Easier maintenance** - Changes to base config apply to all environments
- **Clear separation** - Environment-specific differences are obvious
- **Best practice** - Follows Docker Compose recommended patterns
