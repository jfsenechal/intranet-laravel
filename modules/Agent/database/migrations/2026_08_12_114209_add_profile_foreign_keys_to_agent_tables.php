<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Profiles deleted outside the application (raw SQL) left their children behind,
 * which prevented the `profile_id` foreign keys from being created. Orphans are
 * purged first, then the keys are added with a cascade so it cannot happen again.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const array CHILD_TABLES = [
        'histories',
        'shares',
        'profile_hardware',
        'profile_phone',
        'profile_external_application',
        'profile_folder',
    ];

    protected $connection = 'maria-agent';

    public function up(): void
    {
        foreach (self::CHILD_TABLES as $childTable) {
            DB::connection('maria-agent')
                ->table($childTable)
                ->whereNotNull('profile_id')
                ->whereNotIn('profile_id', fn (QueryBuilder $query) => $query->select('id')->from('profiles'))
                ->delete();

            if ($this->hasProfileForeignKey($childTable)) {
                continue;
            }

            Schema::connection('maria-agent')->table($childTable, function (Blueprint $table): void {
                $table->foreign('profile_id')
                    ->references('id')
                    ->on('profiles')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::CHILD_TABLES as $childTable) {
            if (! $this->hasProfileForeignKey($childTable)) {
                continue;
            }

            Schema::connection('maria-agent')->table($childTable, function (Blueprint $table): void {
                $table->dropForeign(['profile_id']);
            });
        }
    }

    private function hasProfileForeignKey(string $childTable): bool
    {
        return collect(Schema::connection('maria-agent')->getForeignKeys($childTable))
            ->contains(fn (array $foreignKey): bool => $foreignKey['columns'] === ['profile_id']);
    }
};
