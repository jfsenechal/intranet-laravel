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
        if (Schema::connection('maria-activity-manager')->hasTable('activite')) {
            return;
        }
        Schema::connection('maria-activity-manager')->create('activite', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 150);
            $table->longText('description')->nullable();
        });
    }
};
