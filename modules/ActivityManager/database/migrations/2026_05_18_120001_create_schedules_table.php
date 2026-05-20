<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('cours')) {
            Schema::connection('maria-activity-manager')->table('cours', function (Blueprint $table): void {
                $table->rename('schedules');
            });
            Schema::connection('maria-activity-manager')->table('schedules', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
                $table->renameColumn('activite_id', 'activity_id');
            });

            return;
        }
        Schema::connection('maria-activity-manager')->create('schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 200);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->foreignId('activity_id')
                ->nullable()
                ->constrained('activities')
                ->nullOnDelete();
        });
    }
};
