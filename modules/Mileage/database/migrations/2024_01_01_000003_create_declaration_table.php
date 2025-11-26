<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-document')->hasTable('declaration')) {
            return;
        }
        if (Schema::connection('maria-document')->hasTable('declarations')) {
            return;
        }
        Schema::connection('maria-mileage')->create('declarations', function (Blueprint $table) {
            $table->id();
            $table->boolean('omnium')->default(false);
            $table->string('iban');
            $table->string('plaque1');
            $table->string('plaque2')->nullable();
            $table->string('nom');
            $table->string('prenom');
            $table->string('rue');
            $table->string('code_postal');
            $table->string('localite');
            $table->decimal('tarif', 10, 2);
            $table->decimal('tarif_omnium', 10, 2);
            $table->string('user');
            $table->string('type_deplacement');
            $table->date('date_college')->nullable();
            $table->string('article_budgetaire');
            $table->string('departments')->nullable();
            $table->string('user_add');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->dropIfExists('declaration');
    }
};
