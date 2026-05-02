<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('maria-cpasrepas')->create('meals', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->integer('soup_count')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('at_cafeteria')->default(false);
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-cpasrepas')->dropIfExists('meals');
    }
};
