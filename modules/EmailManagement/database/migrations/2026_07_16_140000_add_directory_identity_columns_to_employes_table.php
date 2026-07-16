<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens the mirror to the identity attributes the panel edits.
 *
 * These are the fields the legacy GestEmail edit form carried, restricted to the ones
 * Active Directory actually populates. homePhone and employeeNumber are deliberately left
 * out: neither is set on any entry, and employeeNumber was dropped by an earlier migration
 * because the legacy form had repurposed it to hold a date of birth.
 *
 * All nullable: the directory populates these unevenly, from company on 298 of 299 entries
 * down to initials on one.
 */
return new class extends Migration
{
    /**
     * Column name => whether it holds free text rather than a single line.
     */
    private const array COLUMNS = [
        'initials' => false,
        'wWWHomePage' => false,
        'streetAddress' => false,
        'postOfficeBox' => false,
        'postalCode' => false,
        'l' => false,
        'ipPhone' => false,
        'facsimileTelephoneNumber' => false,
        'mobile' => false,
        'company' => false,
        'title' => false,
        'info' => true,
    ];

    protected $connection = 'maria-email-management';

    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $schema->table('employes', function (Blueprint $table) use ($schema): void {
            foreach (self::COLUMNS as $column => $isText) {
                if ($schema->hasColumn('employes', $column)) {
                    continue;
                }

                if ($isText) {
                    $table->text($column)->nullable();

                    continue;
                }

                $table->string($column)->nullable();
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $existing = array_values(array_filter(
            array_keys(self::COLUMNS),
            fn (string $column): bool => $schema->hasColumn('employes', $column),
        ));

        if ($existing === []) {
            return;
        }

        $schema->table('employes', function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }
};
