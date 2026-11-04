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
     * Guarded: in production the table is imported from data/dovecot_auth.sql, so this only
     * builds it where it does not exist yet (fresh installs and the test database).
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');
        if (! $schema->hasTable('virtual_users')) {
            $schema->create('virtual_users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username', 64)->nullable()->unique();
                $table->string('userPassword')->nullable();
                $table->string('legacy_password')->nullable();
                $table->bigInteger('quota_bytes')->default(0);
                $table->boolean('active')->default(true);
                $table->dateTime('created_at')->nullable()->useCurrent();
                $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-email-management')->dropIfExists('virtual_users');
    }
};
