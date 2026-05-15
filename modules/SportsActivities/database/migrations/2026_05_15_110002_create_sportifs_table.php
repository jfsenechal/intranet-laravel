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
        if (Schema::connection('maria-rescam')->hasTable('sportifs')) {
            return;
        }
        Schema::connection('maria-rescam')->create('sportifs', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 255);
            $table->string('prenom', 255);
            $table->date('ne_le')->nullable();
            $table->string('rue', 255);
            $table->string('code_postal', 255);
            $table->string('localite', 255);
            $table->string('telephone', 255)->nullable();
            $table->string('gsm', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->longText('remarque')->nullable();
            $table->string('user', 255);
            $table->timestamps();
        });
    }
};
