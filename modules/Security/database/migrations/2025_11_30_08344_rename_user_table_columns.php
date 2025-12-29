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
        // Modify column properties in users table only if old columns exist (legacy migration)
        Schema::connection('maria-security')->table('users', function (Blueprint $table) {
            if (Schema::connection('maria-security')->hasColumn('users', 'nom')) {
                $table->renameColumn('nom', 'last_name');
                $table->string('last_name')->nullable(false)->change();
            } else {
                $table->string('last_name')->nullable(false);
            }
            if (Schema::connection('maria-security')->hasColumn('users', 'prenom')) {
                $table->renameColumn('prenom', 'first_name');
                $table->string('first_name')->nullable(false)->change();
            } else {
                $table->string('first_name')->nullable(false);
            }
            if (Schema::connection('maria-security')->hasColumn('users', 'departement')) {
                $table->renameColumn('departement', 'department');
            } else {
                $table->string('department')->nullable();
            }
            if (! Schema::connection('maria-security')->hasColumn('users', 'username')) {
                $table->string('username')->unique();
            }
            $table->string('news_attachment')->nullable(false)->default(false)->change();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('extension', 50)->nullable();
            $table->string('color_primary', 50)->nullable();
            $table->string('color_secondary', 50)->nullable();
            $table->uuid('uuid')->nullable()->change();
            $table->boolean('is_administrator')->default(false);

            if (! Schema::connection('maria-security')->hasColumn('users', 'name')) {
                $table->string('name');
            }
            if (! Schema::connection('maria-security')->hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
            if (! Schema::connection('maria-security')->hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
            if (! Schema::connection('maria-security')->hasColumn('users', 'created_at')) {
                $table->timestamps();
            }
        });

    }
};
