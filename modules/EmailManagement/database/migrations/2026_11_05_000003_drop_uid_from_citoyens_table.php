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
     * The uid was the local part of the mail, which now identifies the citoyen on its own.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if ($schema->hasIndex('citoyens', ['uid'], 'unique')) {
            $schema->table('citoyens', function (Blueprint $table) {
                $table->dropUnique(['uid']);
            });
        }

        if ($schema->hasColumn('citoyens', 'uid')) {
            $schema->table('citoyens', function (Blueprint $table) {
                $table->dropColumn('uid');
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
            $table->string('uid')->nullable()->unique();
        });
    }
};
