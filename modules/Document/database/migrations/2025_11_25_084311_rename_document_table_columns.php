<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'maria-document';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('maria-document')->table('documents', function (Blueprint $table) {
            $table->renameColumn('titre', 'name');
            $table->renameColumn('categorie_id', 'category_id');
            $table->renameColumn('user', 'user_add');
            $table->renameColumn('created', 'created_at');
            $table->renameColumn('updated', 'updated_at');
            $table->renameColumn('fileName', 'file_name');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->softDeletes();
        });

        Schema::connection('maria-document')->table('categorie', function (Blueprint $table) {
            $table->rename('categories');
        });
        Schema::connection('maria-document')->table('categories', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
        });
    }
};
