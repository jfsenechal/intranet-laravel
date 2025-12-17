###SQL

Remove role ROLE_FINANCE_DEPLACEMENT

mkdir -p /var/www/laravel-intranet/data/sqlite && touch /var/www/laravel-intranet/data/sqlite/{intranet,mileage,document,news,publication,courrier})
touch /var/www/laravel-intranet/data/sqlite/{intranet,mileage,document,news,publication,courrier}
php artisan migrate --env testing
php artisan test tests/Feature/Mileage/Policies/BudgetArticlePolicyTest.php --filter="admin can view any budget articles"

### Courrier Module Migration

Migrate user_id to username in incoming_mail_recipient table:
```bash
# Dry run to preview changes
php artisan courrier:migration-incoming-mail-recipient --dry-run

# Execute migration
php artisan courrier:migration-incoming-mail-recipient
```
