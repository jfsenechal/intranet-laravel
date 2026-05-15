<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-rescam';

    public function up(): void
    {
        if (Schema::connection('maria-rescam')->hasTable('inscriptions')) {
            return;
        }
        Schema::connection('maria-rescam')->create('inscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites');
            $table->foreignId('groupe_id')->constrained('groupes');
            $table->foreignId('sportif_id')->constrained('sportifs');
            $table->double('prix')->nullable();
            $table->longText('remarque')->nullable();
            $table->string('user', 255);
            $table->timestamps();

            $table->unique(['activite_id', 'groupe_id', 'sportif_id'], 'inscription_idx');
        });
    }
};
