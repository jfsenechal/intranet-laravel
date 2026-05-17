<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-cpas-library';

    public function up(): void
    {
        if (Schema::connection('maria-cpas-library')->hasTable('Preference')) {
            return;
        }
        Schema::connection('maria-cpas-library')->create('Preference', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->string('value', 255);
            $table->string('username', 255);
            $table->unique(['username', 'name']);
        });
    }
};
