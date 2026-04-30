<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('mariadb')->hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable()->default(null);
                $table->string('first_name')->nullable()->default(null);
                $table->string('last_name')->nullable()->default(null);
                $table->string('username')->nullable()->default(null);
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->json('departments')->nullable(false)->default('[]');
                $table->string('password');
                $table->text('app_authentication_secret')->nullable();
                $table->text('app_authentication_recovery_codes')->nullable();
                $table->rememberToken();
                $table->string('color_primary', 50)->nullable();
                $table->string('color_secondary', 50)->nullable();
                $table->timestamps();
            });
        }

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
};
