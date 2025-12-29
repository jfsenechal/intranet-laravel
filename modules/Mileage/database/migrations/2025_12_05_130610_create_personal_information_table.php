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
        Schema::connection('maria-mileage')->create('personal_information', function (Blueprint $table) {
            $table->id();
            $table->string('car_license_plate1')->nullable();
            $table->string('car_license_plate2')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('username')->unique()->nullable(false);
            $table->date('college_trip_date')->nullable();
            $table->string('iban')->nullable();
            $table->boolean('omnium')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_information');
    }
};
