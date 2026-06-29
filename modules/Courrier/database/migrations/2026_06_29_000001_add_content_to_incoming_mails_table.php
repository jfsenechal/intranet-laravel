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
        if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'content')) {
            return;
        }

        Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
            $table->longText('content')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        if (! Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'content')) {
            return;
        }

        Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
            $table->dropColumn('content');
        });
    }
};
