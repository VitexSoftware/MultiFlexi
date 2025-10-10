#!/bin/bash
# Wait for DB to be available, then run migrations, then start services
set -e

# Load DB connection info
source /etc/multiflexi/database.env

wait_for_db() {
  echo "Waiting for database $DB_HOST:$DB_PORT..."
  for i in {1..60}; do
    if php -r "@mysqli_connect(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'), getenv('DB_PORT')) or exit(1);"; then
      echo "Database is available."
      return 0
    fi
    sleep 2
  done
  echo "Database not available after 120 seconds."
  exit 1
}

wait_for_db

echo "Running multiflexi-migrator..."
multiflexi-migrator || true

echo "Starting cron and apache2..."
/usr/sbin/cron
exec /usr/sbin/apache2ctl -D FOREGROUND