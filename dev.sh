#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== PodKeep Development Setup ===${NC}\n"

# Check if .env exists
if [ ! -f "src/.env" ]; then
    echo -e "${YELLOW}Creating .env file from example...${NC}"
    cp src/.env.example src/.env
    echo -e "${GREEN}✓ Created src/.env${NC}"
    echo -e "${YELLOW}⚠ Please update src/.env with your configuration${NC}\n"
else
    echo -e "${GREEN}✓ .env file exists${NC}\n"
fi

# Check if node_modules exists (first run)
if [ ! -d "src/node_modules" ]; then
    echo -e "${YELLOW}First run detected - this will take a few minutes...${NC}\n"
fi

# Set permissions if needed
if [ "$EUID" -eq 0 ]; then
    echo -e "${GREEN}Setting permissions...${NC}"
    chown -R 82:82 ./src/storage ./src/bootstrap/cache 2>/dev/null || true
    chmod -R 755 ./src/storage ./src/bootstrap/cache 2>/dev/null || true
    echo -e "${GREEN}✓ Permissions set${NC}\n"
fi

# Build and start containers
echo -e "${GREEN}Building and starting containers...${NC}"
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml up -d --build

# Wait for containers to be ready
echo -e "${YELLOW}Waiting for containers to be ready...${NC}"
sleep 5

# Run migrations if database is ready
echo -e "${GREEN}Running migrations...${NC}"
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T app php artisan migrate --force || true

# Clear and cache configs
echo -e "${GREEN}Optimizing application...${NC}"
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T app php artisan optimize:clear || true
docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T app php artisan optimize || true

# Allow commands to be passed
if [ $# -gt 0 ]; then
    case "$1" in
        logs)
            docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml logs -f "${@:2}"
            ;;
        down|stop)
            echo -e "${YELLOW}Stopping development environment...${NC}"
            docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml down
            echo -e "${GREEN}✓ Stopped${NC}"
            ;;
        shell|bash)
            echo -e "${GREEN}Opening shell in app container...${NC}"
            docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml exec app sh
            ;;
        migrate)
            echo -e "${GREEN}Running migrations...${NC}"
            docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml exec app php artisan migrate
            ;;
        test)
            echo -e "${GREEN}Running tests...${NC}"
            docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml exec app php artisan test
            ;;
        restart)
            echo -e "${YELLOW}Restarting containers...${NC}"
            docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml restart
            echo -e "${GREEN}✓ Restarted${NC}"
            ;;
        *)
            echo -e "${RED}Unknown command: $1${NC}"
            echo "Available commands: logs, down, shell, migrate, test, restart"
            exit 1
            ;;
    esac
else
    echo -e "\n${GREEN}=== Development environment is ready! ===${NC}"
    echo -e "\n${GREEN}Application URL:${NC} http://localhost:8000"
    echo -e "${GREEN}View logs:${NC}     ./dev.sh logs"
    echo -e "${GREEN}Stop services:${NC}  ./dev.sh down"
    echo -e "${GREEN}Shell access:${NC}  ./dev.sh shell"
    echo -e "${GREEN}Run migrations:${NC} ./dev.sh migrate"
    echo -e "${GREEN}Run tests:${NC}     ./dev.sh test"
    echo ""
fi
