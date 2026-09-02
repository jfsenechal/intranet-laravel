<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-guichet';

    public function up(): void
    {
        if (Schema::connection('maria-guichet')->hasColumn('reasons', 'service')) {
            return;
        }

        Schema::connection('maria-guichet')->table('reasons', function (Blueprint $table): void {
            $table->string('service')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-guichet')->table('reasons', function (Blueprint $table): void {
            $table->dropColumn('service');
        });
    }
};
