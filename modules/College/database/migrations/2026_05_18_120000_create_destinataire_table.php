<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-college';

    public function up(): void
    {
        if (Schema::connection('maria-college')->hasTable('destinataire')) {
            return;
        }
        Schema::connection('maria-college')->create('destinataire', function (Blueprint $table): void {
            $table->id();
            $table->string('slugname', 70)->unique();
            $table->string('nom', 255);
            $table->string('prenom', 255);
            $table->string('email', 255);
            $table->boolean('pv_service')->default(false);
            $table->boolean('ordre_service')->default(false);
            $table->boolean('ordre_college')->default(false);
            $table->boolean('pv_college')->default(false);
        });
    }
};
