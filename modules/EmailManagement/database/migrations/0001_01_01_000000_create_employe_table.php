<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'maria-email-management';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');
        if (!$schema->hasTable('employes')) {
            $schema->create('employes', function (Blueprint $table) {
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
                $table->float('gosaMailQuota');
                $table->string('homeDirectory');
                $table->string('gosaMailForwardingAddress');
                $table->string('gosaMailAlternateAddress')->nullable();
                $table->string('userPassword')->nullable();
                $table->text('description')->nullable();
                $table->date('last_connection')->nullable();
                $table->string('protocol_connection')->nullable();
                $table->integer('port_connection')->nullable();
                $table->boolean('secure_connection')->nullable();
                $table->timestamp('password_changed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('hands')) {
            $schema->create('hands', function (Blueprint $table) {
                $table->id();
                $table->string('uid')->unique();
                $table->string('email');
                $table->string('password');
                $table->timestamps();
            });
        }

    }
};
