<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    { if (Schema::connection('maria-courrier')->hasTable('categorie')) {
            Schema::connection('maria-courrier')->table('categorie', function (Blueprint $table) {
                $table->rename('categories');
            });
            Schema::connection('maria-courrier')->table('categories', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('couleur', 'color');
                $table->string('color', 7)->default('#6b7280')->change();

            });
        } else {
        Schema::connection('maria-courrier')->create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#6b7280');
            $table->timestamps();
        });
    }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('categories');
    }
};
