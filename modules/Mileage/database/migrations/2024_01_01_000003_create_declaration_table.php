<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-mileage')->hasTable('declaration')) {
            return;
        }
        if (Schema::connection('maria-mileage')->hasTable('declarations')) {
            return;
        }
        Schema::connection('maria-mileage')->create('declarations', function (Blueprint $table) {
            $table->id();
            $table->boolean('omnium')->default(false);
            $table->string('iban');
            $table->string('car_license_plate1');
            $table->string('car_license_plate2')->nullable();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('street');
            $table->string('postal_code');
            $table->string('locality');
            $table->decimal('rate', 10, 2);
            $table->decimal('rate_omnium', 10, 2);
            $table->string('type_movement');
            $table->date('college_date')->nullable();
            $table->string('budget_article');
            $table->string('departments')->nullable();
            $table->string('user_add');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->dropIfExists('declarations');
    }
};
