<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-meal-delivery';

    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('residents')) {
            return;
        }

        Schema::connection('maria-meal-delivery')->create('residents', function (Blueprint $table): void {
            $table->id();
            $table->string('last_name', 100);
            $table->string('first_name', 100)->nullable();
            $table->string('room', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->string('user_add', 100)->nullable();
            $table->timestamps();

            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('residents');
    }
};
