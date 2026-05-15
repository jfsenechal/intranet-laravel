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
        if (Schema::connection('maria-conseil')->hasTable('PieceJointe')) {
            return;
        }
        Schema::connection('maria-conseil')->create('PieceJointe', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('groupe_id')
                ->nullable()
                ->constrained('groupes')
                ->nullOnDelete();
            $table->string('nom', 255);
            $table->string('description', 255)->nullable();
        });
    }
};
