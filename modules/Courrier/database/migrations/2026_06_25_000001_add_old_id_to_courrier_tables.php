<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    /**
     * Tables that receive migrated legacy rows and must keep a reference to
     * their original primary key for traceability.
     *
     * @var list<string>
     */
    private array $tables = [
        'courrier_categories',
        'courrier_services',
        'courrier_senders',
        'recipients',
        'incoming_mails',
        'attachments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::connection('maria-courrier')->hasTable($table)) {
                continue;
            }

            if (Schema::connection('maria-courrier')->hasColumn($table, 'old_id')) {
                continue;
            }

            Schema::connection('maria-courrier')->table($table, function (Blueprint $table): void {
                $table->unsignedInteger('old_id')->nullable()->after('id');
                $table->index('old_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::connection('maria-courrier')->hasColumn($table, 'old_id')) {
                continue;
            }

            Schema::connection('maria-courrier')->table($table, function (Blueprint $table): void {
                $table->dropIndex(['old_id']);
                $table->dropColumn('old_id');
            });
        }
    }
};
