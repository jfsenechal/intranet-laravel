<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('inscription')) {
            return;
        }
        Schema::connection('maria-activity-manager')->create('inscription', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membre_id')
                ->nullable()
                ->constrained('membre')
                ->nullOnDelete();
            $table->foreignId('cours_id')
                ->nullable()
                ->constrained('cours')
                ->nullOnDelete();

            $table->unique(['membre_id', 'cours_id'], 'inscription_membre_cours_unique');
        });
    }
};
