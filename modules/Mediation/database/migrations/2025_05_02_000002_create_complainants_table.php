<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-mediation')->hasTable('plaignant')) {
            Schema::connection('maria-mediation')->table('plaignant', function (Blueprint $table): void {
                $table->rename('complainants');
            });
            Schema::connection('maria-mediation')->table('complainants', function (Blueprint $table): void {
                $table->renameColumn('slugname', 'slug');
                $table->renameColumn('civilite', 'salutation');
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('ne_le', 'birth_date');
                $table->renameColumn('rue', 'street');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('localite', 'city');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection('maria-mediation')->hasTable('complainants')) {
            Schema::connection('maria-mediation')->create('complainants', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 70)->unique();
                $table->string('salutation')->nullable();
                $table->string('last_name');
                $table->string('first_name')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('street');
                $table->string('postal_code');
                $table->string('city');
                $table->string('user_add');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-mediation')->dropIfExists('complainants');
    }
};
