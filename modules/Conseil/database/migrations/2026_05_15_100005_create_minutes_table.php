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
        if (Schema::connection('maria-conseil')->hasTable('pv')) {
            Schema::connection('maria-conseil')->table('pv', function (Blueprint $table): void {
                $table->rename('minutes');
            });
            Schema::connection('maria-conseil')->table('minutes', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('date_pv', 'meeting_date');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
            });
        } elseif (! Schema::connection('maria-conseil')->hasTable('minutes')) {
            Schema::connection('maria-conseil')->create('minutes', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100);
                $table->date('meeting_date');
                $table->string('file_name', 50);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-conseil')->dropIfExists('minutes');
    }
};
