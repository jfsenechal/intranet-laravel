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
        if (Schema::connection('maria-conseil')->hasTable('groupes')) {
            return;
        }
        Schema::connection('maria-conseil')->create('groupes', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 255);
        });
    }
};
