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
        if (Schema::connection('maria-conseil')->hasTable('groupe_destinataire')) {
            return;
        }
        Schema::connection('maria-conseil')->create('groupe_destinataire', function (Blueprint $table): void {
            $table->foreignId('destinataire_id')
                ->constrained('destinataires')
                ->cascadeOnDelete();
            $table->foreignId('groupe_id')
                ->constrained('groupes')
                ->cascadeOnDelete();
            $table->primary(['groupe_id', 'destinataire_id']);
        });
    }
};
