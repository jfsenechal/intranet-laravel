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
        if (Schema::connection('maria-conseil')->hasTable('ordre_jour')) {
            return;
        }
        Schema::connection('maria-conseil')->create('ordre_jour', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 150);
            $table->dateTime('date_ordre');
            $table->date('date_fin_diffusion')->nullable();
            $table->string('file_name', 120);
            $table->timestamp('createdAt');
            $table->timestamp('updatedAt');
        });
    }
};
