<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('maria-cpasrepas')->create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('salutation')->nullable();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('slug', 70)->unique();
            $table->string('street');
            $table->string('number');
            $table->integer('postal_code');
            $table->string('city');
            $table->string('floor')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_notes')->nullable();
            $table->text('notes')->nullable();
            $table->text('recurring_order')->nullable();
            $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->text('route_backup')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('use_cafeteria')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-cpasrepas')->dropIfExists('clients');
    }
};
