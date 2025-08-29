# Marsa Maroc Port Management System - Docker Setup

## Quick Start

### Prerequisites
- Docker Desktop installed on Windows
- Git (optional, for version control)

### 1. Start the Application
```bash
docker-compose up -d
```

### 2. Access the Application
- **Web Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MySQL Database**: localhost:3306

### 3. Default Database Credentials
- **Database**: gestion_operations_portuaires
- **Username**: marsa_user
- **Password**: marsa_password_123
- **Root Password**: root_password_123

## Services Overview

### Web Service (Apache + PHP 8.2)
- **Container**: marsa-maroc-web
- **Port**: 8080
- **Document Root**: /var/www/html
- **PHP Extensions**: PDO, MySQLi, GD, ZIP

### MySQL Database Service
- **Container**: marsa-maroc-mysql
- **Port**: 3306
- **Version**: MySQL 8.0
- **Persistent Storage**: Docker volume `mysql_data`

### phpMyAdmin (Database Management)
- **Container**: marsa-maroc-phpmyadmin
- **Port**: 8081
- **Purpose**: Web-based database administration

## Docker Commands

### Start Services
```bash
# Start all services in background
docker-compose up -d

# Start with logs visible
docker-compose up

# Start specific service
docker-compose up -d web
```

### Stop Services
```bash
# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: deletes database data)
docker-compose down -v
```

### View Logs
```bash
# View all logs
docker-compose logs

# View specific service logs
docker-compose logs web
docker-compose logs mysql

# Follow logs in real-time
docker-compose logs -f web
```

### Database Management
```bash
# Access MySQL container
docker-compose exec mysql mysql -u root -p

# Import SQL file
docker-compose exec mysql mysql -u root -p gestion_operations_portuaires < backup.sql

# Export database
docker-compose exec mysql mysqldump -u root -p gestion_operations_portuaires > backup.sql
```

### Development Commands
```bash
# Rebuild containers after code changes
docker-compose build

# Restart services
docker-compose restart

# View running containers
docker-compose ps

# Access web container shell
docker-compose exec web bash
```

## Directory Structure
```
/
├── docker/
│   ├── apache/
│   │   └── 000-default.conf     # Apache virtual host config
│   └── php/
│       └── php.ini              # PHP configuration
├── database/
│   ├── schema.sql               # Database schema (auto-loaded)
│   └── init.sh                  # Initialization script
├── config/
│   └── database.php             # Docker-compatible DB config
├── docker-compose.yml           # Main Docker configuration
├── Dockerfile                   # Web container definition
└── .dockerignore               # Docker build optimization
```

## Environment Variables

The application uses these environment variables (automatically set by Docker Compose):

- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_NAME=gestion_operations_portuaires`
- `DB_USER=marsa_user`
- `DB_PASSWORD=marsa_password_123`

## Troubleshooting

### Database Connection Issues
1. Ensure MySQL container is running: `docker-compose ps`
2. Check MySQL logs: `docker-compose logs mysql`
3. Verify database config in `config/database.php`

### Port Conflicts
If ports 8080, 8081, or 3306 are in use:
1. Stop conflicting services
2. Or modify ports in `docker-compose.yml`

### Permission Issues
```bash
# Fix file permissions (run from project root)
docker-compose exec web chown -R www-data:www-data /var/www/html
docker-compose exec web chmod -R 755 /var/www/html
```

### Reset Database
```bash
# Stop services and remove database volume
docker-compose down -v

# Start services (will recreate database)
docker-compose up -d
```

## Development Workflow

1. **Make code changes** in your local files
2. **Refresh browser** - changes are reflected immediately
3. **For PHP configuration changes**: `docker-compose restart web`
4. **For database schema changes**: Update `database/schema.sql` and reset database

## Production Considerations

For production deployment, modify:
1. Change database passwords in `docker-compose.yml`
2. Disable error display in `docker/php/php.ini`
3. Remove phpMyAdmin service
4. Use external database service
5. Add SSL/HTTPS configuration

## Backup Strategy

### Database Backup
```bash
# Create backup
docker-compose exec mysql mysqldump -u root -p gestion_operations_portuaires > "backup_$(date +%Y%m%d_%H%M%S).sql"

# Restore backup
docker-compose exec -T mysql mysql -u root -p gestion_operations_portuaires < backup_file.sql
```

### File Backup
All application files are in your project directory and automatically backed up with your version control system.
