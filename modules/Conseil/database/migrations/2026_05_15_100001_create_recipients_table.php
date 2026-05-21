<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-conseil';

    public function up(): void
    {
        if (Schema::connection('maria-conseil')->hasTable('destinataire')) {
            Schema::connection('maria-conseil')->table('destinataire', function (Blueprint $table): void {
                $table->rename('conseil_recipients');
            });
            Schema::connection('maria-conseil')->table('conseil_recipients', function (Blueprint $table): void {
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
            });
        } elseif (! Schema::connection('maria-conseil')->hasTable('conseil_recipients')) {
            Schema::connection('maria-conseil')->create('conseil_recipients', function (Blueprint $table): void {
                $table->id();
                $table->string('last_name', 255);
                $table->string('first_name', 255);
                $table->string('email', 255);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-conseil')->dropIfExists('conseil_recipients');
    }
};
