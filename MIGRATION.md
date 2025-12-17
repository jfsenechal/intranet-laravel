###SQL

Remove role ROLE_FINANCE_DEPLACEMENT

mkdir -p /var/www/laravel-intranet/data/sqlite && touch /var/www/laravel-intranet/data/sqlite/{intranet,mileage,document,news,publication,courrier})
touch /var/www/laravel-intranet/data/sqlite/{intranet,mileage,document,news,publication,courrier}
php artisan migrate --env testing
php artisan test tests/Feature/Mileage/Policies/BudgetArticlePolicyTest.php --filter="admin can view any budget articles"
