<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-conseil';

    public function up(): void
    {
        if (Schema::connection('maria-conseil')->hasTable('groupe')) {
            Schema::connection('maria-conseil')->table('groupe', function (Blueprint $table): void {
                $table->rename('groups');
            });
            Schema::connection('maria-conseil')->table('groups', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
            });
        } elseif (! Schema::connection('maria-conseil')->hasTable('groups')) {
            Schema::connection('maria-conseil')->create('groups', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 255);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-conseil')->dropIfExists('groups');
    }
};
