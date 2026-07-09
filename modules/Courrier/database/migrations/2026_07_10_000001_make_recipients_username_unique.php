<?php

declare(strict_types=1);

use AcMarche\Courrier\Handler\RecipientDeduplicator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        // The legacy CPAS/BGM merge produced recipients duplicated by username;
        // collapse them before the column can carry a unique constraint.
        (new RecipientDeduplicator)->run();

        RecipientDeduplicator::addUniqueUsernameIndex();
    }

    public function down(): void
    {
        if (! RecipientDeduplicator::hasUniqueUsernameIndex()) {
            return;
        }

        Schema::connection('maria-courrier')->table('recipients', function (Blueprint $table): void {
            $table->dropUnique('recipients_username_unique');
        });
    }
};
