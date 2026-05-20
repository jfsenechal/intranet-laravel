<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('inscription')) {
            Schema::connection('maria-activity-manager')->table('inscription', function (Blueprint $table): void {
                $table->rename('registrations');
            });
            Schema::connection('maria-activity-manager')->table('registrations', function (Blueprint $table): void {
                $table->renameColumn('membre_id', 'member_id');
                $table->renameColumn('cours_id', 'schedule_id');
            });

            return;
        }
        Schema::connection('maria-activity-manager')->create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();
            $table->foreignId('schedule_id')
                ->nullable()
                ->constrained('schedules')
                ->nullOnDelete();

            $table->unique(['member_id', 'schedule_id'], 'inscription_membre_cours_unique');
        });
    }
};
