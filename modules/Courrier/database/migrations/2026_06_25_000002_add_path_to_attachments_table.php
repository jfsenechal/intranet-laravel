<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Attachment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (! Schema::connection('maria-courrier')->hasColumn('attachments', 'path')) {
            Schema::connection('maria-courrier')->table('attachments', function (Blueprint $table): void {
                $table->string('path')->nullable()->after('file_name');
            });
        }

        Attachment::backfillLegacyPaths();
    }

    public function down(): void
    {
        if (Schema::connection('maria-courrier')->hasColumn('attachments', 'path')) {
            Schema::connection('maria-courrier')->table('attachments', function (Blueprint $table): void {
                $table->dropColumn('path');
            });
        }
    }
};
