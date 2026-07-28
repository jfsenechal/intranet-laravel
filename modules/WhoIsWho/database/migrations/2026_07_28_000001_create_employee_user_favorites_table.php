<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_user_favorites', function (Blueprint $table): void {
            $table->id();
            // `users.id` and `employees.id` are legacy signed `int(11)` columns, so
            // the default `foreignId()` (bigint unsigned) cannot reference them.
            $table->integer('user_id');
            // HRM employees live on the `maria-hrm` connection, so no foreign key here.
            $table->integer('employee_id');
            $table->timestamps();
            $table->unique(['user_id', 'employee_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_user_favorites');
    }
};
