<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'maria-db';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mariadb')->table('users', function (Blueprint $table) {
            $table->string('name');
            $table->string('email')->unique();
            $table->renameColumn('nom', 'last_name');
            $table->renameColumn('prenom', 'first_name');
            $table->renameColumn('departement', 'department');
            $table->string('last_name')->nullable(false);
            $table->string('first_name')->nullable(false);
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('extension')->nullable();
            $table->string('username')->unique()->nullable(false);
            $table->string('color_primary')->nullable();
            $table->string('color_secondary')->nullable();
            $table->uuid('uuid')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::connection('mariadb')->table('heading', function (Blueprint $table) {
            $table->rename('headings');
        });
        Schema::connection('mariadb')->table('module', function (Blueprint $table) {
            $table->rename('modules');
        });

        Schema::connection('mariadb')->table('headings', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->renameColumn('icone', 'icon');
        });
        Schema::connection('mariadb')->table('modules', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->renameColumn('icone', 'icon');
            $table->renameColumn('exterieur', 'is_external');
            $table->renameColumn('icone', 'icon');
        });
    }
};
