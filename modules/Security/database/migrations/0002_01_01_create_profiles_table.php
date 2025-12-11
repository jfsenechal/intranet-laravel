<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-security';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-security')->hasTable('profiles')) {
            Schema::connection('maria-security')->table('profile', function (Blueprint $table) {
                $table->rename('profiles');
            });
            Schema::connection('maria-security')->table('profiles', function (Blueprint $table) {
                $table->renameColumn('plaque1', 'car_license_plate1');
                $table->renameColumn('plaque2', 'car_license_plate2');
                $table->renameColumn('rue', 'street');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('localite', 'city');
                $table->renameColumn('deplacement_date_college', 'college_trip_date');
            });
        } else {
            Schema::create('profiles', function (Blueprint $table) {
                $table->id();
                $table->string('car_license_plate1')->nullable();
                $table->string('car_license_plate2')->nullable();
                $table->string('street')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('city')->nullable();
                $table->string('college_trip_date')->nullable();
            });
        }
    }
};
