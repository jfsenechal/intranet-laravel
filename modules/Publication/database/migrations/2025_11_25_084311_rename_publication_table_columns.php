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
        if (!Schema::connection('maria-publication')->hasTable('publications')) {
            if (Schema::connection('maria-publication')->hasTable('publication')) {
                Schema::connection('maria-publication')->table('publication', function (Blueprint $table) {
                    $table->rename('publications');
                });
            }
            Schema::connection('maria-publication')->table('publications', function (Blueprint $table) {
                $table->renameColumn('title', 'name');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
                $table->string('user_add');
                $table->softDeletes();
            });
        }

        if (!Schema::connection('maria-publication')->hasTable('categories')) {
            Schema::connection('maria-publication')->table('category', function (Blueprint $table) {
                $table->rename('categories');
            });
        }
    }
};
