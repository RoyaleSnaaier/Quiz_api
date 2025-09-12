# Docker Configuration

This folder contains all Docker-related files for the Quiz API.

## Files:

- **`Dockerfile`** - PHP 8.2 with Apache container configuration
- **`apache-config.conf`** - Apache virtual host configuration for clean URLs

## Usage:

From the root directory, run:

```bash
# Build and start containers
docker-compose up -d

# View logs
docker-compose logs -f api

# Stop containers
docker-compose down

# Rebuild containers
docker-compose up --build -d
```

## Container Details:

- **Base Image**: php:8.2-apache
- **PHP Extensions**: mysqli, pdo, pdo_mysql
- **Apache Modules**: mod_rewrite (enabled)
- **Working Directory**: /var/www/html
- **Exposed Port**: 80