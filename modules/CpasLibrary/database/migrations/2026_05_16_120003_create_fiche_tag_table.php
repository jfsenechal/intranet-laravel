<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-cpas-library';

    public function up(): void
    {
        if (Schema::connection('maria-cpas-library')->hasTable('fiche_tag')) {
            return;
        }
        Schema::connection('maria-cpas-library')->create('fiche_tag', function (Blueprint $table): void {
            $table->foreignId('fiche_id')
                ->constrained('fiche')
                ->cascadeOnDelete();
            $table->foreignId('tag_id')
                ->constrained('tag')
                ->cascadeOnDelete();
            $table->primary(['fiche_id', 'tag_id']);
        });
    }
};
