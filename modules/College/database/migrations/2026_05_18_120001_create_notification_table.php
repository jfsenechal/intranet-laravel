<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-college';

    public function up(): void
    {
        if (Schema::connection('maria-college')->hasTable('notification')) {
            return;
        }
        Schema::connection('maria-college')->create('notification', function (Blueprint $table): void {
            $table->id();
            $table->string('file_name', 255);
            $table->string('mime', 255);
            $table->dateTime('updatedAt');
        });
    }
};
