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
        if (Schema::connection('maria-rescam')->hasTable('activites')) {
            return;
        }
        Schema::connection('maria-rescam')->create('activites', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 255);
            $table->longText('description')->nullable();
            $table->string('user', 255);
            $table->boolean('archive')->default(false);
            $table->timestamps();
        });
    }
};
