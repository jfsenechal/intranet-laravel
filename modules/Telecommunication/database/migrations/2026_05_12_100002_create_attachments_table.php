<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-telecommunication';

    public function up(): void
    {
        $schema = Schema::connection('maria-telecommunication');

        if ($schema->hasTable('attachments')) {
            $schema->table('attachments', function (Blueprint $table): void {
                $table->rename('telecommunication_attachments');
            });
            if ($schema->hasColumn('telecommunication_attachments', 'updatedAt')) {
                $schema->table('telecommunication_attachments', function (Blueprint $table): void {
                    $table->renameColumn('updatedAt', 'updated_at');
                });
            }

            if (! $schema->hasColumn('telecommunication_attachments', 'created_at')) {
                $schema->table('telecommunication_attachments', function (Blueprint $table): void {
                    $table->timestamp('created_at')->nullable();
                });
            }

            return;
        }

        if ($schema->hasTable('telecommunication_attachments')) {
            return;
        }

        $schema->create('telecommunication_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('telephone_id')
                ->constrained('telephones')
                ->cascadeOnDelete();
            $table->string('file_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-telecommunication')->dropIfExists('telecommunication_attachments');
    }
};
