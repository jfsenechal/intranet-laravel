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
     * The password hashes now live in virtual_users.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        foreach (['userPassword', 'legacy_password'] as $column) {
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
     * Restores the columns only: the dropped hashes are not recovered.
     */
    public function down(): void
    {
        Schema::connection('maria-email-management')->table('citoyens', function (Blueprint $table) {
            $table->string('userPassword')->nullable();
            $table->string('legacy_password')->nullable();
        });
    }
};
