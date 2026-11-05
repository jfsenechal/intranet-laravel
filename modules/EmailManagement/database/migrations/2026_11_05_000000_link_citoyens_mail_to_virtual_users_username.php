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
     * Every citoyen must have a Dovecot login. A rename of the login follows through to the
     * citoyen, and a login still used by a citoyen cannot be deleted. This fails if a citoyen
     * has no login: those rows have to be fixed by hand, they are never removed here.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        $schema->table('virtual_users', function (Blueprint $table) {
            $table->string('username', 64)->nullable(false)->change();
        });

        $schema->table('citoyens', function (Blueprint $table) {
            $table->foreign('mail')
                ->references('username')
                ->on('virtual_users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('maria-email-management');

        $schema->table('citoyens', function (Blueprint $table) {
            $table->dropForeign(['mail']);
        });

        $schema->table('virtual_users', function (Blueprint $table) {
            $table->string('username', 64)->nullable()->change();
        });
    }
};
