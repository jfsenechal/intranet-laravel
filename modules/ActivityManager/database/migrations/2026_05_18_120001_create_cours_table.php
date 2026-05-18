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
        if (Schema::connection('maria-activity-manager')->hasTable('cours')) {
            return;
        }
        Schema::connection('maria-activity-manager')->create('cours', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 200);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->foreignId('activite_id')
                ->nullable()
                ->constrained('activite')
                ->nullOnDelete();
        });
    }
};
