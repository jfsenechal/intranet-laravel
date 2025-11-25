<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-document';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('maria-document')->table('documents', function (Blueprint $table) {
            // Rename old columns to new Laravel convention names
            $table->renameColumn('titre', 'title');
            $table->renameColumn('categorie_id', 'category_id');
            $table->renameColumn('user', 'user_add');
            $table->renameColumn('created', 'created_at');
            $table->renameColumn('updated', 'updated_at');
            $table->renameColumn('fileName', 'file_name');
        });
    }
};
