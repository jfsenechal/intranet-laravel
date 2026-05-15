#!/bin/bash
# DB_HOST=127.0.0.1 DB_USER=root DB_PASS=your-password ./drop_tables.sh
# Database credentials - update these with your actual credentials
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"

# Build password argument only if DB_PASS is set
if [ -n "$DB_PASS" ]; then
  DB_PASS_ARG=(-p"$DB_PASS")
else
  DB_PASS_ARG=()
fi

# List of databases to clean
DATABASES=("intranet" "actu" "document" "finance" "publication" "indicateur_ville" "grh_all" "pst" "mailinglist" "agent" "cpas_repas" "mediation" "invitation" "sanction" "telephone" "guichet" "conseil")

# Function to drop all tables in a database
drop_all_tables() {
  local db_name=$1

  echo "Processing database: $db_name"

  # Get all table names
  TABLES=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" "${DB_PASS_ARG[@]}" -Nse "SELECT GROUP_CONCAT(table_name SEPARATOR ',') FROM information_schema.tables WHERE
table_schema='$db_name'")

  if [ -z "$TABLES" ]; then
      echo "No tables found in database: $db_name"
      return
  fi

  # Disable foreign key checks and drop all tables
  mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" "${DB_PASS_ARG[@]}" "$db_name" <<EOF
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
  mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" "${DB_PASS_ARG[@]}" "$db_name" < "$sql_file"
  echo "SQL dump imported for database: $db_name"
}

# Loop through each database
for db in "${DATABASES[@]}"; do
  drop_all_tables "$db"
  import_sql_dump "$db"
  echo "---"
done

mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" "${DB_PASS_ARG[@]}" "pst" -e "SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS \`cache\`, \`cache_locks\`, \`failed_jobs\`, \`jobs\`, \`job_batches\`, \`migrations\`, \`notifications\`, \`password_reset_tokens\`, \`personal_access_tokens\`, \`roles\`, \`role_user\`, \`sessions\`, \`users\`; SET FOREIGN_KEY_CHECKS = 1;"

echo "All tables dropped and SQL dumps imported for specified databases."
php artisan migrate
echo "Migrate and db seed done."
php artisan intranet:sync-users
echo "Sync users done."
php artisan db:seed
echo "DB seed done."
php artisan intranet:migration-role
echo "Migration role done."
php artisan intranet:sync-role
echo "Sync role done."
php artisan mileage:migration
echo "Migration done."
exit
php artisan pst:migration
echo "Pst done."
php artisan courrier:migration
echo "Courrier done."
