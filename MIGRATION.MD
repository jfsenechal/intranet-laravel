
Remove role ROLE_FINANCE_DEPLACEMENT
Remove role ROLE_INDICATEUR_CPAS ROLE_INDICATEUR_BOURGMESTRE ROLE_INDICATEUR_VILLE

Retirer de la db modules les indicateurs cpas et bourgmestre

### Courrier Module Migration
Migrate user_id to username in incoming_mail_recipient table:
```bash
# Dry run to preview changes
php artisan courrier:migration-incoming-mail-recipient --dry-run

# Execute migration
php artisan courrier:migration-incoming-mail-recipient
```

### Mileage Module Migration
Migrate profile from intranet database to mileage database
Done in drop_tables.sh

### Roles migration
Done in drop_tables.sh
