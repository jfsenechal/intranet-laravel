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
        if (Schema::connection('maria-rescam')->hasTable('members')) {
            return;
        }

        Schema::connection('maria-rescam')->create('members', function (Blueprint $table): void {
            $table->id();
            $table->string('last_name', 255);
            $table->string('first_name', 255);
            $table->date('birth_date')->nullable();
            $table->string('street', 255);
            $table->string('postal_code', 255);
            $table->string('city', 255);
            $table->string('phone', 255)->nullable();
            $table->string('mobile', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->longText('comment')->nullable();
            $table->timestamps();
        });
    }
};
