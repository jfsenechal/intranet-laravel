<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the local mirror of the staff directory: the panel now reads Active Directory live.
 *
 * Deliberately has no $connection: the table may be on the mail server or on the intranet
 * database, and neither should keep a copy, so both are named explicitly.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('maria-email-management')->dropIfExists('employes');
        Schema::dropIfExists('employes');
    }

    /**
     * Reverse the migrations.
     *
     * Restores the empty table on the intranet database: the rows are not recovered, they
     * came from Active Directory.
     */
    public function down(): void
    {
        Schema::create('employes', function (Blueprint $table): void {
            $table->id();
            $table->string('samaccountname')->nullable()->unique();
            $table->string('dn')->unique();
            $table->string('mail')->nullable()->unique();
            $table->string('telephoneNumber')->nullable();
            $table->string('givenName')->nullable();
            $table->string('sn')->nullable();
            $table->string('cn')->nullable();
            $table->string('displayName')->nullable();
            $table->text('description')->nullable();
            $table->date('last_connection')->nullable();
            $table->string('protocol_connection')->nullable();
            $table->integer('port_connection')->nullable();
            $table->boolean('secure_connection')->nullable();
            $table->timestamps();
            $table->timestamp('sync_at')->nullable();
            $table->string('initials')->nullable();
            $table->string('wWWHomePage')->nullable();
            $table->string('streetAddress')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('l')->nullable();
            $table->string('ipPhone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('company')->nullable();
            $table->string('title')->nullable();
            $table->text('info')->nullable();
            $table->string('department')->nullable();
            $table->string('co')->nullable();
        });
    }
};
