<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-rescam';

    public function up(): void
    {
        if (Schema::connection('maria-rescam')->hasTable('groupe')) {
            Schema::connection('maria-rescam')->table('groupe', function (Blueprint $table): void {
                $table->rename('sports_groups');
            });
            Schema::connection('maria-rescam')->table('sports_groups', function (Blueprint $table): void {
                $table->renameColumn('jour', 'day');
                $table->renameColumn('heure', 'time');
                $table->renameColumn('lieux', 'location');
                $table->renameColumn('prix', 'price');
                $table->renameColumn('remarque', 'comment');
                $table->renameColumn('activite_id', 'activity_id');
            });
        } elseif (!Schema::connection('maria-rescam')->hasTable('sports_groups')) {
            Schema::connection('maria-rescam')->create('sports_groups', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('activity_id')
                    ->constrained('sports_activities');
                $table->string('day', 255);
                $table->string('time', 255);
                $table->string('location', 255);
                $table->string('age', 255);
                $table->double('price')->default(0);
                $table->longText('description')->nullable();
                $table->longText('comment')->nullable();
                $table->timestamps();
            });
        }
    }
};
