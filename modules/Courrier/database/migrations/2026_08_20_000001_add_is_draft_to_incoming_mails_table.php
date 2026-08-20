<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'is_draft')) {
            return;
        }

        Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
            $table->boolean('is_draft')->default(false)->after('is_notified');
            // Drafts are excluded from the day's listing, the notifications and
            // the index, so every one of those queries filters on this column.
            $table->index('is_draft');
        });
    }

    public function down(): void
    {
        if (! Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'is_draft')) {
            return;
        }

        Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
            $table->dropIndex(['is_draft']);
            $table->dropColumn('is_draft');
        });
    }
};
