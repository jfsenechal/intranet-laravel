<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-mailing-list';

    public function up(): void
    {
        if (Schema::connection('maria-mailing-list')->hasTable('contacts')) {
            return;
        }
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->index();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('email')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }
};
