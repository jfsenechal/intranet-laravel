<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('note')) {
            Schema::connection('maria-meal-delivery')->table('note', function (Blueprint $table): void {
                $table->rename('notes');
            });
            Schema::connection('maria-meal-delivery')->table('notes', function (Blueprint $table): void {
                $table->renameColumn('dateNote', 'note_date');
                $table->renameColumn('isDone', 'is_done');
                $table->renameColumn('doneBy', 'done_by');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('notes')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('note_date');
            $table->text('description');
            $table->boolean('is_done')->default(false);
            $table->string('done_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('notes');
    }
};
