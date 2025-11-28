<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class  {
    protected $connection = 'mariadb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mariadb')->table('heading', function (Blueprint $table) {
            $table->rename('headings');
        });
        Schema::connection('mariadb')->table('module', function (Blueprint $table) {
            $table->rename('modules');
        });

        Schema::connection('mariadb')->table('headings', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->renameColumn('icone', 'icon');
        });
        Schema::connection('mariadb')->table('modules', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->renameColumn('icone', 'icon');
            $table->renameColumn('exterieur', 'is_external');
            $table->renameColumn('icone', 'icon');
        });
    }
};

