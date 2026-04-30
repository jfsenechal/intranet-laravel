<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasColumn = fn (string $column): bool => Schema::hasColumn('users', $column);

        Schema::table('users', function (Blueprint $table) use ($hasColumn): void {
            if ($hasColumn('departement')) {
                $table->dropColumn('departement');
            }

            if (! $hasColumn('departments')) {
                $table->json('departments')->nullable(false);
            }

            if ($hasColumn('nom')) {
                $table->renameColumn('nom', 'last_name');
                $table->string('last_name')->nullable(false)->change();
            } elseif ($hasColumn('last_name')) {
                $table->string('last_name')->nullable(false)->change();
            } else {
                $table->string('last_name')->nullable(false);
            }

            if ($hasColumn('prenom')) {
                $table->renameColumn('prenom', 'first_name');
                $table->string('first_name')->nullable(false)->change();
            } elseif ($hasColumn('first_name')) {
                $table->string('first_name')->nullable(false)->change();
            } else {
                $table->string('first_name')->nullable(false);
            }

            if (! $hasColumn('phone')) {
                $table->string('phone', 50)->nullable();
            }

            if (! $hasColumn('mobile')) {
                $table->string('mobile', 50)->nullable();
            }

            if (! $hasColumn('extension')) {
                $table->string('extension', 50)->nullable();
            }

            if (! $hasColumn('color_primary')) {
                $table->string('color_primary', 50)->nullable();
            }

            if (! $hasColumn('color_secondary')) {
                $table->string('color_secondary', 50)->nullable();
            }

            if (! $hasColumn('uuid')) {
                $table->uuid()->nullable();
            }

            if (! $hasColumn('is_administrator')) {
                $table->boolean('is_administrator')->default(false);
            }

            if (! $hasColumn('app_authentication_secret')) {
                $table->text('app_authentication_secret')->nullable();
            }

            if (! $hasColumn('app_authentication_recovery_codes')) {
                $table->text('app_authentication_recovery_codes')->nullable();
            }
            if (! $hasColumn('email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }

            if (! $hasColumn('remember_token')) {
                $table->rememberToken();
            }

            if (! $hasColumn('created_at')) {
                $table->timestamps();
            }

            if (! $hasColumn('mandatory')) {
                $table->tinyInteger('mandatory')->default(0);
            }

            if ($hasColumn('news_attachment')) {
                $table->boolean('news_attachment')->nullable(false)->default(false)->change();
            }
        });
    }
};
