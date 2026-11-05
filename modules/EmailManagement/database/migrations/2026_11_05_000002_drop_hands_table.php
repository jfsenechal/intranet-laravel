<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-email-management';

    /**
     * Run the migrations.
     *
     * The original password now lives on citoyens.hand_password. A row left here is the only
     * copy of a citizen's password, so the table is never dropped while it holds one.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('hands')) {
            return;
        }

        if (DB::connection('maria-email-management')->table('hands')->exists()) {
            throw new RuntimeException('La table hands contient encore des mots de passe : restaurez-les avant de la supprimer.');
        }

        $schema->drop('hands');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-email-management')->create('hands', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
    }
};
