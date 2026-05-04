<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('maria-cpasrepas')->create('route_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('route_id')->constrained('delivery_routes')->cascadeOnDelete();
            $table->foreignId('client_id')->unique()->constrained('clients')->cascadeOnDelete();
            $table->integer('position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-cpasrepas')->dropIfExists('route_orders');
    }
};
