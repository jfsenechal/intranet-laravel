<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the two identity attributes the panel does not offer for editing.
 *
 * Both are sparse in the directory -- postOfficeBox on 16 of 299 entries and
 * facsimileTelephoneNumber on 12 -- and neither is worth a column in the mirror now that the
 * form has stopped exposing them. Active Directory keeps its own values either way: nothing
 * writes to these attributes once they are off LDAP_IDENTITY_ATTRIBUTES.
 */
return new class extends Migration
{
    private const array COLUMNS = ['postOfficeBox', 'facsimileTelephoneNumber'];

    protected $connection = 'maria-email-management';

    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $existing = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => $schema->hasColumn('employes', $column),
        ));

        if ($existing === []) {
            return;
        }

        $schema->table('employes', function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $schema->table('employes', function (Blueprint $table) use ($schema): void {
            foreach (self::COLUMNS as $column) {
                if (! $schema->hasColumn('employes', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }
};
