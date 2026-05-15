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
        if (Schema::connection('maria-conseil')->hasTable('pvs')) {
            return;
        }
        Schema::connection('maria-conseil')->create('pvs', function (Blueprint $table): void {
            $table->id();
            $table->string('nom', 100);
            $table->date('date_pv');
            $table->string('file_name', 50);
            $table->timestamp('createdAt');
            $table->timestamp('updatedAt');
        });
    }
};
