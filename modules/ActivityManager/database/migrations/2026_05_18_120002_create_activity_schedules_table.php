<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('dates_cours')) {
            Schema::connection('maria-activity-manager')->table('dates_cours', function (Blueprint $table): void {
                $table->rename('activity_schedules');
                $table->renameColumn('jour', 'schedule_date');
                $table->renameColumn('remarque', 'comment');
                $table->renameColumn('cours_id', 'schedule_id');
            });

            return;
        }
        Schema::connection('maria-activity-manager')->create('activity_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schedule_id')
                ->constrained('schedules')
                ->cascadeOnDelete();
            $table->longText('comment')->nullable();
            $table->dateTime('schedule_date');
        });
    }
};
