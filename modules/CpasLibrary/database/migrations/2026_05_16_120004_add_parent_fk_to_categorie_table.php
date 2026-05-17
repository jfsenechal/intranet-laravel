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
        Schema::connection('maria-cpas-library')->table('categorie', function (Blueprint $table): void {
            $table->foreign('parent_id')
                ->references('id')->on('categorie')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-cpas-library')->table('categorie', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
        });
    }
};
