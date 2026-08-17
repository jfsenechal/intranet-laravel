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
        if (Schema::connection('maria-guichet')->hasColumn('offices', 'color')) {
            return;
        }

        Schema::connection('maria-guichet')->table('offices', function (Blueprint $table): void {
            $table->string('color')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-guichet')->table('offices', function (Blueprint $table): void {
            $table->dropColumn('color');
        });
    }
};
