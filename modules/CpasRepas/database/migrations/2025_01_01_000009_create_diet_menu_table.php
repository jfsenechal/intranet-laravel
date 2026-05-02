<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('maria-cpasrepas')->create('diet_menu', function (Blueprint $table): void {
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('diet_id')->constrained('diets')->cascadeOnDelete();
            $table->primary(['menu_id', 'diet_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-cpasrepas')->dropIfExists('diet_menu');
    }
};
