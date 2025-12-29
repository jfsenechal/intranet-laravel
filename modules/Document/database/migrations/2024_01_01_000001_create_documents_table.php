<?php

use AcMarche\Document\Models\Category;
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
        if (Schema::connection('maria-document')->hasTable('document')) {
            Schema::connection('maria-document')->table('document', function (Blueprint $table) {
                $table->rename('documents');
            });
            Schema::connection('maria-document')->table('documents', function (Blueprint $table) {
                $table->renameColumn('titre', 'name');
                $table->renameColumn('categorie_id', 'category_id');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('fileName', 'file_name');
                $table->string('file_path');
                $table->integer('file_size')->nullable();
                $table->string('file_mime')->nullable();
                $table->softDeletes();
            });
        } elseif (! Schema::connection('maria-document')->hasTable('documents')) {
            Schema::connection('maria-document')->create('documents', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->text('content')->nullable();
                $table->string('file_path');
                $table->string('file_name');
                $table->integer('file_size')->nullable();
                $table->string('file_mime')->nullable();
                $table->string('category')->nullable();
                $table->string('user_add');
                $table->foreignIdFor(Category::class);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (Schema::connection('maria-document')->hasTable('categorie')) {
            Schema::connection('maria-document')->table('categorie', function (Blueprint $table) {
                $table->rename('categories');
            });
            Schema::connection('maria-document')->table('categories', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
            });
        } elseif (! Schema::connection('maria-document')->hasTable('categories')) {
            Schema::connection('maria-document')->create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-document')->dropIfExists('documents');
        Schema::connection('maria-document')->dropIfExists('categories');
    }
};
