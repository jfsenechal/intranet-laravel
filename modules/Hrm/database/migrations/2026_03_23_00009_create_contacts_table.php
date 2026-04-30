<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        if (Schema::connection('maria-hrm')->hasTable('contacts')) {
            return;
        }
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('last_name');
            $table->string('first_name')->nullable();
            $table->string('email_1')->unique();
            $table->string('phone_1')->nullable();
            $table->string('email_2')->unique();
            $table->string('phone_2')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
};
