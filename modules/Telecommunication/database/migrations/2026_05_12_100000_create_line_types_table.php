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

        if ($schema->hasTable('type_ligne')) {
            $schema->table('type_ligne', function (Blueprint $table): void {
                $table->rename('line_types');
            });
            $schema->table('line_types', function (Blueprint $table): void {
                $table->renameColumn('slugname', 'slug');
            });
        } elseif (! $schema->hasTable('line_types')) {
            $schema->create('line_types', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 70)->unique();
                $table->string('name');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-telecommunication')->dropIfExists('line_types');
    }
};
