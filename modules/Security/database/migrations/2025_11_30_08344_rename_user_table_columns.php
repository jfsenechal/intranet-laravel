<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mariadb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::connection('mariadb')->hasTable('heading')) {
            // Rename tables first
            Schema::connection('mariadb')->table('heading', function (Blueprint $table) {
                $table->rename('tabs');
                // Rename columns in tabs table
                Schema::connection('mariadb')->table('tabs', function (Blueprint $table) {
                    $table->renameColumn('nom', 'name');
                    $table->renameColumn('icone', 'icon');
                });
            });
        }

        if (! Schema::connection('mariadb')->hasTable('profiles')) {
            Schema::connection('mariadb')->table('profile', function (Blueprint $table) {
                $table->rename('profiles');
                Schema::connection('mariadb')->table('profiles', function (Blueprint $table) {
                    $table->renameColumn('plaque1', 'car_license_plate1');
                    $table->renameColumn('plaque2', 'car_license_plate2');
                    $table->renameColumn('rue', 'street');
                    $table->renameColumn('code_postal', 'postal_code');
                    $table->renameColumn('localite', 'city');
                    $table->renameColumn('deplacement_date_college', 'college_trip_date');
                });
            });
        }

        if (! Schema::connection('mariadb')->hasTable('modules')) {
            Schema::connection('mariadb')->table('module', function (Blueprint $table) {
                $table->rename('modules');
            });
            // Rename columns in modules table
            Schema::connection('mariadb')->table('modules', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('exterieur', 'is_external');
                $table->renameColumn('public', 'is_public');
                $table->renameColumn('icone', 'icon');
                $table->renameColumn('heading_id', 'tab_id');
                $table->string('color')->default(null);
            });
        }

        // Modify column properties in users table only if old columns exist (legacy migration)

        Schema::connection('mariadb')->table('users', function (Blueprint $table) {
            if (Schema::connection('mariadb')->hasColumn('users', 'nom')) {
                $table->renameColumn('nom', 'last_name');
                $table->string('last_name')->nullable(false)->change();
            } else {
                $table->string('last_name')->nullable(false);
            }
            if (Schema::connection('mariadb')->hasColumn('users', 'prenom')) {
                $table->renameColumn('prenom', 'first_name');
                $table->string('first_name')->nullable(false)->change();
            } else {
                $table->string('first_name')->nullable(false);
            }
            if (Schema::connection('mariadb')->hasColumn('users', 'departement')) {
                $table->renameColumn('departement', 'department');
            } else {
                $table->string('department')->nullable();
            }

            $table->string('news_attachment')->nullable(false)->default(false)->change();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('extension', 50)->nullable();
            $table->string('color_primary', 50)->nullable();
            $table->string('color_secondary', 50)->nullable();
            $table->uuid('uuid')->nullable()->change();
            $table->boolean('is_administrator')->default(false);

            if (! Schema::connection('mariadb')->hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
            if (! Schema::connection('mariadb')->hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
            if (! Schema::connection('mariadb')->hasColumn('users', 'created_at')) {
                $table->timestamps();
            }
        });

    }
};
