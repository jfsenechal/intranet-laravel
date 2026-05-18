<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('membre')) {
            return;
        }
        Schema::connection('maria-activity-manager')->create('membre', function (Blueprint $table): void {
            $table->id();
            $table->string('civilite', 50)->nullable();
            $table->string('nom', 50);
            $table->string('prenom', 50);
            $table->string('rue', 150)->nullable();
            $table->string('numero', 50)->nullable();
            $table->integer('codepostal')->nullable();
            $table->string('localite', 50)->nullable();
            $table->string('gsm', 50)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->boolean('enabled')->default(true);
            $table->longText('remarque')->nullable();
            $table->date('inscrit_le')->nullable();
        });
    }
};
