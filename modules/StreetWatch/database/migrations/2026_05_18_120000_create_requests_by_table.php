<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-street-watch';

    public function up(): void
    {
        if (Schema::connection('maria-street-watch')->hasTable('requests_by')) {
            return;
        }
        Schema::connection('maria-street-watch')->create('requests_by', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
        });
    }
};
