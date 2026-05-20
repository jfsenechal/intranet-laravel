<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-rescam';

    public function up(): void
    {
        if (Schema::connection('maria-rescam')->hasTable('groups')) {
            return;
        }

        Schema::connection('maria-rescam')->create('groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_id')
                ->constrained('activities');
            $table->string('day', 255);
            $table->string('time', 255);
            $table->string('location', 255);
            $table->string('age', 255);
            $table->double('price')->default(0);
            $table->longText('description')->nullable();
            $table->longText('comment')->nullable();
            $table->string('user', 255);
            $table->timestamps();
        });
    }
};
