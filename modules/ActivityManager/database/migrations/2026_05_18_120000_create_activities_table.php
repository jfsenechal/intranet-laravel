<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('activite')) {
            Schema::connection('maria-activity-manager')->table('activite', function (Blueprint $table): void {
                $table->rename('activities');
            });
            Schema::connection('maria-activity-manager')->table('activities', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
            });

            return;
        }
        Schema::connection('maria-activity-manager')->create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->longText('description')->nullable();
        });
    }
};
