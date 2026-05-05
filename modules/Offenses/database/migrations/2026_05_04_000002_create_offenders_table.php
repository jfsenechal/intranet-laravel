<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-offenses')->hasTable('contrevenant')) {
            Schema::connection('maria-offenses')->table('contrevenant', function (Blueprint $table): void {
                $table->rename('offenders');
            });
            Schema::connection('maria-offenses')->table('offenders', function (Blueprint $table): void {
                $table->renameColumn('slugname', 'slug');
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
        } elseif (! Schema::connection('maria-offenses')->hasTable('offenders')) {
            Schema::connection('maria-offenses')->create('offenders', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 70)->unique();
                $table->string('last_name');
                $table->string('first_name');
                $table->date('birth_date')->nullable();
                $table->string('street')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('city')->nullable();
                $table->string('user_add')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-offenses')->dropIfExists('offenders');
    }
};
