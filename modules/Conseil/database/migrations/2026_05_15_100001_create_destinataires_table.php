<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-conseil';

    public function up(): void
    {
        if (Schema::connection('maria-conseil')->hasTable('destinataires')) {
            return;
        }
        Schema::connection('maria-conseil')->create('destinataires', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 255);
            $table->string('prenom', 255);
            $table->string('email', 255);
        });
    }
};
