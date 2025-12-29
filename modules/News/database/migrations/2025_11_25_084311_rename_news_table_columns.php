<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-news';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::connection('maria-news')->hasTable('news')) {
            Schema::connection('maria-news')->table('news', function (Blueprint $table) {
                // Rename old columns to new Laravel convention names
                $table->renameColumn('titre', 'name');
                $table->renameColumn('categorie_id', 'category_id');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('date_end', 'end_date');
                $table->renameColumn('departement', 'department');
                $table->renameColumn('sended', 'sent');
                $table->boolean('sent')->default(false)->change();
                $table->json('medias')->nullable();
                $table->string('slug')->unique()->nullable();
                $table->text('excerpt')->nullable();
                $table->softDeletes();
                /* foreach (range(1, 3) as $i) {
                     $table->renameColumn('attach'.$i.'Name', 'file_name_'.$i);
                 }*/
                /**
                 * Attachments
                 * foreach (range(1, 3) as $i) {
                 * $table->string('file_path_'.$i);
                 * $table->integer('file_size_'.$i)->nullable();
                 * $table->string('mime_type_'.$i)->nullable();
                 * }*/
            });
        }

        if (! Schema::connection('maria-news')->hasTable('categories')) {
            Schema::connection('maria-news')->table('categorie', function (Blueprint $table) {
                $table->rename('categories');
            });
            Schema::connection('maria-news')->table('categories', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('couleur', 'color');
                $table->renameColumn('icone', 'icon');
            });
        }
    }
};
