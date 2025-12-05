<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('maria-mileage')->create('personal_information', function (Blueprint $table) {
            $table->id();
            $table->string('car_license_plate1');
            $table->string('car_license_plate2')->nullable();
            $table->string('street');
            $table->string('postal_code');
            $table->string('city');
            $table->string('username')->unique()->nullable(false);
            $table->date('college_trip_date');
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
