<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-meal-delivery';

    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('guest_reservations')) {
            return;
        }

        Schema::connection('maria-meal-delivery')->create('guest_reservations', function (Blueprint $table): void {
            $table->id();
            // `clients.id` is a signed int(11) inherited from the legacy Symfony
            // schema, so `foreignId()` (bigint unsigned) cannot reference it.
            $table->integer('client_id');
            $table->date('date');
            $table->integer('menu1_count')->default(0);
            $table->integer('menu2_count')->default(0);
            $table->text('notes')->nullable();
            $table->string('user_add', 100)->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'date']);
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('guest_reservations');
    }
};
