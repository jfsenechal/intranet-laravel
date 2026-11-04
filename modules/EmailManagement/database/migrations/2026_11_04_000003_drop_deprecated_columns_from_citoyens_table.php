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
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if ($schema->hasIndex('citoyens', ['dn'], 'unique')) {
            $schema->table('citoyens', function (Blueprint $table) {
                $table->dropUnique(['dn']);
            });
        }

        foreach (['cn', 'dn', 'gosaMailForwardingAddress', 'gosaMailAlternateAddress', 'password_changed_at'] as $column) {
            if ($schema->hasColumn('citoyens', $column)) {
                $schema->table('citoyens', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Restores the columns only: the dropped values are not recovered.
     */
    public function down(): void
    {
        Schema::connection('maria-email-management')->table('citoyens', function (Blueprint $table) {
            $table->string('cn')->nullable();
            $table->string('dn')->nullable()->unique();
            $table->string('gosaMailForwardingAddress')->nullable();
            $table->string('gosaMailAlternateAddress')->nullable();
            $table->timestamp('password_changed_at')->nullable();
        });
    }
};
