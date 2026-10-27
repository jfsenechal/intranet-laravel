<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-email-management';

    /**
     * Run the migrations.
     *
     * Guarded: in production the table is imported from data/citoyens.sql, so this only
     * builds it where it does not exist yet (fresh installs and the test database).
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');
        if (! $schema->hasTable('citoyens')) {
            $schema->create('citoyens', function (Blueprint $table) {
                $table->id();
                $table->string('uid')->unique();
                $table->string('dn')->unique();
                $table->string('mail')->unique();
                $table->string('givenName')->nullable();
                $table->string('sn')->nullable();
                $table->string('cn')->nullable();
                $table->string('l')->nullable();
                $table->string('postalAddress')->nullable();
                $table->string('employeeNumber')->nullable();
                $table->string('postalCode')->nullable();
                $table->double('gosaMailQuota');
                $table->string('homeDirectory');
                $table->string('gosaMailForwardingAddress');
                $table->string('gosaMailAlternateAddress')->nullable();
                $table->string('userPassword')->nullable();
                $table->string('legacy_password')->nullable();
                $table->text('description')->nullable();
                $table->date('last_connection')->nullable();
                $table->timestamps();
                $table->string('auth_token')->nullable()->unique();
                $table->rememberToken();
                $table->timestamp('charter_accepted_at')->nullable();
                $table->string('recovery_email')->nullable();
                $table->string('recovery_phone')->nullable();
                $table->timestamp('password_changed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-email-management')->dropIfExists('citoyens');
    }
};
