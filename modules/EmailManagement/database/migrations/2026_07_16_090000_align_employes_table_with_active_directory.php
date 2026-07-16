<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moves the employes mirror off the OpenLDAP/GOSA schema and onto Active Directory.
 *
 * The GOSA attributes have no counterpart in AD, and the "espace citoyen" columns
 * served a portal this application does not route. The table holds no rows, so the
 * drops are not destructive.
 *
 * Each change is guarded so this runs correctly both on an existing database and on a
 * fresh install, where the original create migration lays down the old shape first.
 */
return new class extends Migration
{
    /**
     * Columns belonging to the GOSA mail schema or the unrouted citizen portal.
     */
    private const array DROPPED_COLUMNS = [
        'uid',
        'l',
        'postalAddress',
        'postalCode',
        'employeeNumber',
        'gosaMailQuota',
        'homeDirectory',
        'gosaMailForwardingAddress',
        'gosaMailAlternateAddress',
        'userPassword',
        'password_changed_at',
    ];

    protected $connection = 'maria-email-management';

    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $schema->table('employes', function (Blueprint $table) use ($schema): void {
            if (! $schema->hasColumn('employes', 'samaccountname')) {
                $table->string('samaccountname')->nullable()->unique()->after('id');
            }

            if (! $schema->hasColumn('employes', 'displayName')) {
                $table->string('displayName')->nullable()->after('cn');
            }

            if (! $schema->hasColumn('employes', 'telephoneNumber')) {
                $table->string('telephoneNumber')->nullable()->after('mail');
            }

            if (! $schema->hasColumn('employes', 'sync_at')) {
                $table->timestamp('sync_at')->nullable();
            }
        });

        // uid carries a unique index. MariaDB drops it along with the column, sqlite
        // (used by the test suite) refuses the drop while the index still references it.
        if ($schema->hasColumn('employes', 'uid')) {
            $schema->table('employes', function (Blueprint $table): void {
                $table->dropUnique('employes_uid_unique');
            });
        }

        $existing = array_values(array_filter(
            self::DROPPED_COLUMNS,
            fn (string $column): bool => $schema->hasColumn('employes', $column),
        ));

        if ($existing !== []) {
            $schema->table('employes', function (Blueprint $table) use ($existing): void {
                $table->dropColumn($existing);
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $schema->table('employes', function (Blueprint $table): void {
            $table->dropColumn(['samaccountname', 'displayName', 'telephoneNumber', 'sync_at']);

            $table->string('uid')->nullable();
            $table->string('l')->nullable();
            $table->string('postalAddress')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('employeeNumber')->nullable();
            $table->float('gosaMailQuota')->nullable();
            $table->string('homeDirectory')->nullable();
            $table->string('gosaMailForwardingAddress')->nullable();
            $table->string('gosaMailAlternateAddress')->nullable();
            $table->string('userPassword')->nullable();
            $table->timestamp('password_changed_at')->nullable();
        });
    }
};
