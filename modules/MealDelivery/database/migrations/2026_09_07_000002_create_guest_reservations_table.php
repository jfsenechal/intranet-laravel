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
        $schema = Schema::connection('maria-meal-delivery');

        if ($schema->hasTable('guest_reservations') && $schema->hasColumn('guest_reservations', 'resident_id')) {
            return;
        }

        // The first version of this table hung guest meals off `clients`. Guests
        // belong to residents of the home, who are never clients of the meal
        // delivery service, so the client-based table is dropped rather than
        // migrated — it never held a single row.
        $schema->dropIfExists('guest_reservations');

        $schema->create('guest_reservations', function (Blueprint $table): void {
            $table->id();
            // `residents` is created by this module, so it carries the Laravel
            // default bigint unsigned key: unlike `clients`, `foreignId()` works.
            $table->foreignId('resident_id')
                ->constrained('residents')
                ->cascadeOnDelete();
            $table->date('date');
            $table->integer('menu1_count')->default(0);
            $table->integer('menu2_count')->default(0);
            $table->text('notes')->nullable();
            $table->string('user_add', 100)->nullable();
            $table->timestamps();

            $table->unique(['resident_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('guest_reservations');
    }
};
