# MultiFlexi Docker Setup

This directory contains Docker configuration files for running MultiFlexi in a containerized environment.

## Services

The Docker Compose setup includes the following services:

- **web**: MultiFlexi web interface (Apache + PHP)
- **executor**: MultiFlexi job executor daemon (runs `/usr/lib/multiflexi-executor/daemon.php`)
- **scheduler**: MultiFlexi job scheduler daemon (runs `/usr/lib/multiflexi-scheduler/daemon.php`)
- **db**: MySQL 8.0 database
- **redis**: Redis cache server

## Quick Start

1. Copy the environment templates:
   ```bash
   cp .env.example .env
   cp multiflexi.env.example multiflexi.env
   ```

2. Edit the configuration files:
   ```bash
   nano .env              # Docker Compose environment
   nano multiflexi.env    # MultiFlexi application environment
   ```

3. Start the services:
   ```bash
   docker-compose up -d
   ```

4. Access the web interface at `http://localhost:8080`

## Configuration

### Docker Compose Environment (`.env`)

Docker Compose specific settings:

- `WEB_PORT`: Web interface port (default: 8080)
- `DB_PORT`: Database port exposure (default: 3306)
- `MYSQL_ROOT_PASSWORD`: MySQL root password
- Database connection settings

### MultiFlexi Environment (`multiflexi.env`)

Application-specific settings that match the systemd service configuration:

- `DB_CONNECTION`: Database type (mysql or sqlite)
- `DB_HOST`: Database host
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `MULTIFLEXI_DEBUG`: Debug mode (true/false)
- `MULTIFLEXI_LOG_LEVEL`: Log level (info, debug, warning, error)

## Service Architecture

The executor and scheduler services are based on the systemd service definitions:

### Executor Service
- **SystemD**: `/usr/lib/systemd/system/multiflexi-executor.service`
- **Command**: `/usr/bin/php /usr/lib/multiflexi-executor/daemon.php`
- **Purpose**: Executes scheduled MultiFlexi jobs
- **User**: multiflexi:multiflexi

### Scheduler Service  
- **SystemD**: `/usr/lib/systemd/system/multiflexi-scheduler.service`
- **Command**: `/usr/bin/php /usr/lib/multiflexi-scheduler/daemon.php`
- **Purpose**: Manages job scheduling and timing
- **User**: multiflexi:multiflexi

Both services use PHP daemon processes (no cron required) and read environment configuration from `/etc/multiflexi/multiflexi.env`.

## Volumes

The setup uses named volumes for data persistence:

- `db_data`: MySQL database files
- `redis_data`: Redis data
- `web_logs`: Apache logs
- `executor_logs`: Executor logs  
- `scheduler_logs`: Scheduler logs
- `sqlite_data`: SQLite database (if using SQLite)
- `config_data`: MultiFlexi configuration files

## Management Commands

### View logs
```bash
docker-compose logs -f [service_name]
```

### Stop services
```bash
docker-compose down
```

### Rebuild images
```bash
docker-compose build --no-cache
```

### Database backup
```bash
docker-compose exec db mysqldump -u root -p multiflexi > backup.sql
```

### Check service status
```bash
docker-compose ps
```

## Package Installation

The Dockerfiles use `apt install multiflexi-*` packages from the VitexSoftware repository. This ensures:

- **Consistent deployments**: Same packages as system installations
- **Proper dependencies**: All required PHP modules and extensions
- **System integration**: Proper file locations and permissions
- **Security updates**: Official package updates and patches

## Troubleshooting

### Service won't start
Check the logs:
```bash
docker-compose logs [service_name]
```

### Database connection issues
1. Ensure the database service is healthy:
   ```bash
   docker-compose ps
   ```
2. Check database connectivity:
   ```bash
   docker-compose exec executor php -r "echo 'DB test';"
   ```

### Environment file issues
Verify the `multiflexi.env` file is properly mounted:
```bash
docker-compose exec executor cat /etc/multiflexi/multiflexi.env
```

### Port conflicts
Change the `WEB_PORT` or `DB_PORT` in your `.env` file if ports are already in use.

## Differences from SystemD

When running in Docker vs systemd:

1. **No systemd**: Services run as foreground processes instead of systemd services
2. **User management**: multiflexi user created in container (matches systemd config)
3. **Environment files**: Uses same `/etc/multiflexi/multiflexi.env` format
4. **Commands**: Identical PHP daemon commands as systemd ExecStart
5. **No cron**: Modern MultiFlexi uses PHP daemons, not cron jobs
