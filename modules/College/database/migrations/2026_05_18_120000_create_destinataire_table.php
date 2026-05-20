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
            Schema::connection('maria-college')->table('destinataire', function (Blueprint $table): void {
                $table->rename('recipients');
            });
            Schema::connection('maria-college')->table('recipients', function (Blueprint $table): void {
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
            });
        } elseif (! Schema::connection('maria-college')->hasTable('recipients')) {
            Schema::connection('maria-college')->create('recipients', function (Blueprint $table): void {
                $table->id();
                $table->string('last_name', 255);
                $table->string('first_name', 255);
                $table->string('email', 255);
                $table->boolean('pv_service')->default(false);
                $table->boolean('ordre_service')->default(false);
                $table->boolean('ordre_college')->default(false);
                $table->boolean('pv_college')->default(false);
            });
        }
    }
};
