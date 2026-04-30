<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('signatures')) {
            return;
        }

        Schema::create('signatures', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('address');
            $table->unsignedSmallInteger('postal_code');
            $table->string('city');
            $table->string('service')->nullable();
            $table->string('job_title')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_title')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
