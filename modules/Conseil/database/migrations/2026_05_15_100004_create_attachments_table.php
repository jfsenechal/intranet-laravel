<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-conseil';

    public function up(): void
    {
        if (Schema::connection('maria-conseil')->hasTable('PieceJointe')) {
            Schema::connection('maria-conseil')->table('PieceJointe', function (Blueprint $table): void {
                $table->rename('conseil_attachments');
            });
            Schema::connection('maria-conseil')->table('conseil_attachments', function (Blueprint $table): void {
                $table->renameColumn('groupe_id', 'group_id');
                $table->renameColumn('nom', 'name');
            });
        } elseif (! Schema::connection('maria-conseil')->hasTable('conseil_attachments')) {
            Schema::connection('maria-conseil')->create('conseil_attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('group_id')
                    ->nullable()
                    ->constrained('groups')
                    ->nullOnDelete();
                $table->string('name', 255);
                $table->string('description', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-conseil')->dropIfExists('conseil_attachments');
    }
};
