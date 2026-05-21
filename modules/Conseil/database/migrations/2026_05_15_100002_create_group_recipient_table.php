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
        if (Schema::connection('maria-conseil')->hasTable('groupe_destinataire')) {
            Schema::connection('maria-conseil')->table('groupe_destinataire', function (Blueprint $table): void {
                $table->rename('group_recipient');
            });
            Schema::connection('maria-conseil')->table('group_recipient', function (Blueprint $table): void {
                $table->renameColumn('groupe_id', 'group_id');
                $table->renameColumn('destinataire_id', 'recipient_id');
            });
        } elseif (! Schema::connection('maria-conseil')->hasTable('group_recipient')) {
            Schema::connection('maria-conseil')->create('group_recipient', function (Blueprint $table): void {
                $table->foreignId('group_id')
                    ->constrained('groups')
                    ->cascadeOnDelete();
                $table->foreignId('recipient_id')
                    ->constrained('conseil_recipients')
                    ->cascadeOnDelete();
                $table->primary(['group_id', 'recipient_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-conseil')->dropIfExists('group_recipient');
    }
};
