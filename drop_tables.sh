#!/bin/bash
# DB_HOST=127.0.0.1 DB_USER=root DB_PASS=your-password ./drop_tables.sh
# Database credentials - update these with your actual credentials
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"

# List of databases to clean
DATABASES=("intranet" "actu" "document" "finance" "publication")
#DATABASES=("publication")

# Function to drop all tables in a database
drop_all_tables() {
  local db_name=$1

  echo "Processing database: $db_name"

  # Get all table names
  TABLES=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" -Nse "SELECT GROUP_CONCAT(table_name SEPARATOR ',') FROM information_schema.tables WHERE
table_schema='$db_name'")

  if [ -z "$TABLES" ]; then
      echo "No tables found in database: $db_name"
      return
  fi

  # Disable foreign key checks and drop all tables
  mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$db_name" <<EOF
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS $TABLES;
SET FOREIGN_KEY_CHECKS = 1;
EOF

  echo "Dropped all tables in database: $db_name"
}

# Function to import SQL dump
import_sql_dump() {
  local db_name=$1
  local sql_file="data/dumpsql/${db_name}.sql"

  if [ ! -f "$sql_file" ]; then
    echo "SQL dump file not found: $sql_file"
    return
  fi

  echo "Importing SQL dump for database: $db_name"
  mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$db_name" < "$sql_file"
  echo "SQL dump imported for database: $db_name"
}

# Loop through each database
for db in "${DATABASES[@]}"; do
  drop_all_tables "$db"
  import_sql_dump "$db"
  echo "---"
done

echo "All tables dropped and SQL dumps imported for specified databases."
php artisan migrate
php artisan db:seed
echo "Migrate and db seed done."
php artisan intranet:migration-role
