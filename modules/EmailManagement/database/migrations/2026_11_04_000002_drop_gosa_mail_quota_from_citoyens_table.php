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
     * The quota is now read from virtual_users.quota_bytes.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if ($schema->hasColumn('citoyens', 'gosaMailQuota')) {
            $schema->table('citoyens', function (Blueprint $table) {
                $table->dropColumn('gosaMailQuota');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Restores the column only: the dropped values are not recovered.
     */
    public function down(): void
    {
        Schema::connection('maria-email-management')->table('citoyens', function (Blueprint $table) {
            $table->double('gosaMailQuota')->default(0);
        });
    }
};
