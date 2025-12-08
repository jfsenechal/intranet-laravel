<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'mariadb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename tables first
        Schema::connection('mariadb')->table('heading', function (Blueprint $table) {
            $table->rename('tabs');
        });

        Schema::connection('mariadb')->table('profile', function (Blueprint $table) {
            $table->rename('profils');
        });

        // Rename columns in tabs table
        Schema::connection('mariadb')->table('tabs', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->renameColumn('icone', 'icon');
        });

        // Modify column properties in users table
        Schema::connection('mariadb')->table('users', function (Blueprint $table) {
            $table->renameColumn('nom', 'last_name');
            $table->renameColumn('prenom', 'first_name');
            $table->renameColumn('departement', 'department');
            $table->string('name', 255)->nullable(false);
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('news_attachment')->nullable(false)->default(false)->change();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('extension', 50)->nullable();
            $table->string('color_primary', 50)->nullable();
            $table->string('color_secondary', 50)->nullable();
            $table->uuid('uuid')->nullable()->change();
            $table->boolean('is_administrator')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::connection('mariadb')->table('profils', function (Blueprint $table) {
            $table->renameColumn('plaque1', 'car_license_plate1');
            $table->renameColumn('plaque2', 'car_license_plate2');
            $table->renameColumn('rue', 'street');
            $table->renameColumn('code_postal', 'postal_code');
            $table->renameColumn('localite', 'city');
            $table->renameColumn('deplacement_date_college', 'college_trip_date');
        });
    }
};
