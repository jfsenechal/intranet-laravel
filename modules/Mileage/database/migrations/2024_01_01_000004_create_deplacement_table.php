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
        Schema::connection('maria-mileage')->create('deplacement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaration_id')->nullable()->constrained('declaration')->onDelete('set null');
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('distance');
            $table->dateTime('date_depart');
            $table->dateTime('date_arrive')->nullable();
            $table->time('heure_start')->nullable();
            $table->time('heure_end')->nullable();
            $table->text('content');
            $table->decimal('tarif', 10, 2)->nullable();
            $table->decimal('omnium', 10, 2)->nullable();
            $table->decimal('repas', 10, 2)->nullable();
            $table->decimal('train', 10, 2)->nullable();
            $table->string('type_deplacement');
            $table->string('lieu_depart')->nullable();
            $table->string('lieu_arrive')->nullable();
            $table->string('user_add');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->dropIfExists('deplacement');
    }
};
