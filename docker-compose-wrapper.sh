#!/bin/bash

# Docker Compose wrapper for development and production

set -e

COMPOSE_FILES="-f docker-compose.base.yml"

case "$1" in
  dev)
    COMPOSE_FILES="$COMPOSE_FILES -f docker-compose.dev.yml"
    shift
    docker-compose $COMPOSE_FILES "$@"
    ;;
  prod|production)
    COMPOSE_FILES="$COMPOSE_FILES -f docker-compose.prod.yml"
    shift
    docker-compose $COMPOSE_FILES "$@"
    ;;
  *)
    echo "Usage: $0 {dev|prod} [docker-compose commands]"
    echo ""
    echo "Examples:"
    echo "  $0 dev up -d          # Start development"
    echo "  $0 dev logs -f        # View dev logs"
    echo "  $0 prod build         # Build production image"
    echo "  $0 prod up -d         # Start production"
    echo "  $0 dev down           # Stop development"
    echo "  $0 prod down          # Stop production"
    exit 1
    ;;
esac
