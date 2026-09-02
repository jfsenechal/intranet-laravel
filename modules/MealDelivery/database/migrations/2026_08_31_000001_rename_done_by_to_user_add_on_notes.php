<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('maria-meal-delivery');

        if (! $schema->hasTable('notes') || $schema->hasColumn('notes', 'user_add')) {
            return;
        }

        if ($schema->hasColumn('notes', 'done_by')) {
            $schema->table('notes', function (Blueprint $table): void {
                $table->renameColumn('done_by', 'user_add');
            });

            return;
        }

        $schema->table('notes', function (Blueprint $table): void {
            $table->string('user_add', 100)->nullable();
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-meal-delivery');

        if (! $schema->hasTable('notes') || ! $schema->hasColumn('notes', 'user_add')) {
            return;
        }

        $schema->table('notes', function (Blueprint $table): void {
            $table->renameColumn('user_add', 'done_by');
        });
    }
};
