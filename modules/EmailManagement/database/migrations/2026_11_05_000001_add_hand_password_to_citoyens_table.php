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
     * Holds the hash of the citoyen's own password while an admin has taken over the mailbox.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasColumn('citoyens', 'hand_password')) {
            $schema->table('citoyens', function (Blueprint $table) {
                $table->string('hand_password')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-email-management')->table('citoyens', function (Blueprint $table) {
            $table->dropColumn('hand_password');
        });
    }
};
