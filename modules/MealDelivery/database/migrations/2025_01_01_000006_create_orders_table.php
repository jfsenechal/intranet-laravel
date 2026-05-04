<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('maria-meal-delivery')->create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('week_id')->nullable()->constrained('weeks')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_last_meal')->default(false);
            $table->timestamps();
            $table->unique(['week_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('orders');
    }
};
