<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'maria-publication';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('maria-publication')->table('publication', function (Blueprint $table) {
            $table->rename('publications');
        });

        Schema::connection('maria-publication')->table('publications', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
            $table->renameColumn('created', 'created_at');
            $table->renameColumn('updated', 'updated_at');
            $table->softDeletes();
        });

        Schema::connection('maria-publication')->table('category', function (Blueprint $table) {
            $table->rename('categories');
        });
    }
};
