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
        if (Schema::connection('maria-conseil')->hasTable('ordre_jour')) {
            Schema::connection('maria-conseil')->table('ordre_jour', function (Blueprint $table): void {
                $table->rename('agendas');
            });
            Schema::connection('maria-conseil')->table('agendas', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('date_ordre', 'agenda_date');
                $table->renameColumn('date_fin_diffusion', 'distribution_end_date');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
            });
        } elseif (! Schema::connection('maria-conseil')->hasTable('agendas')) {
            Schema::connection('maria-conseil')->create('agendas', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 150);
                $table->dateTime('agenda_date');
                $table->date('distribution_end_date')->nullable();
                $table->string('file_name', 120);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-conseil')->dropIfExists('agendas');
    }
};
