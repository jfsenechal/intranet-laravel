<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The `slug` columns inherited from the legacy `slugname` columns were never read
 * by the application, yet they are NOT NULL without a default, so every insert
 * that did not set one failed. Nothing generated them either, which broke record
 * creation on these tables.
 *
 * `employers.slug` is deliberately kept: HrmAuthorization resolves permissions
 * from the top-level employer slug ("ville" / "cpas") and `hrm:reminders` looks
 * up its department root the same way.
 *
 * @see AcMarche\Hrm\Policies\Concerns\HrmAuthorization
 */
return new class extends Migration
{
    protected $connection = 'maria-hrm';

    /**
     * @var array<int, string>
     */
    private array $tables = [
        'directions',
        'services',
        'employees',
        'contract_types',
        'contract_natures',
    ];

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        foreach ($this->tables as $table) {
            if ($schema->hasColumn($table, 'slug')) {
                $schema->table($table, function (Blueprint $blueprint): void {
                    $blueprint->dropColumn('slug');
                });
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        $lengths = [
            'directions' => 80,
            'services' => 80,
            'employees' => 62,
            'contract_types' => 75,
            'contract_natures' => 75,
        ];

        foreach ($this->tables as $table) {
            if (! $schema->hasColumn($table, 'slug')) {
                $schema->table($table, function (Blueprint $blueprint) use ($lengths, $table): void {
                    $blueprint->string('slug', $lengths[$table])->default('');
                });
            }
        }
    }
};
