#!/bin/bash
# DB_HOST=127.0.0.1 DB_USER=root DB_PASS=your-password ./drop_tables.sh
# Database credentials - update these with your actual credentials
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"

# List of databases to clean
DATABASES=("actu" "document" "finance" "laravel_intranet","publication")

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

# Loop through each database
for db in "${DATABASES[@]}"; do
  drop_all_tables "$db"
  echo "---"
done

echo "All tables dropped from specified databases."
