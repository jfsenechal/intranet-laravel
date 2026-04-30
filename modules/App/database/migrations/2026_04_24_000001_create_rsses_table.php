<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rsses')) {
            return;
        }

        Schema::create('rsses', function (Blueprint $table): void {
            $table->id();
            $table->string('username');
            $table->string('name');
            $table->string('url');
            $table->timestamps();

            $table->unique(['username', 'url']);
            $table->foreign('username')->references('username')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsses');
    }
};
