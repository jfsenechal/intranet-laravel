<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-note';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-note');

        if ($schema->hasColumn('notes', 'is_encrypted')) {
            return;
        }

        $schema->table('notes', function (Blueprint $table): void {
            $table->boolean('is_encrypted')->default(false)->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('maria-note');

        if (! $schema->hasColumn('notes', 'is_encrypted')) {
            return;
        }

        $schema->table('notes', function (Blueprint $table): void {
            $table->dropColumn('is_encrypted');
        });
    }
};
