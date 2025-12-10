<?php

use AcMarche\News\Models\Category;
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
        if (Schema::connection('maria-news')->hasTable('news')) {
            return;
        }

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->string('end_date');
            $table->text('content');
            $table->string('department');
            $table->string('user_add')->nullable();
            $table->boolean('archive')->default(false);
            $table->boolean('sent')->default(false);
            $table->json('medias')->nullable();
            $table->timestamps();
            $table->foreignIdFor(Category::class);
            $table->softDeletes();

            /**
             * Attachments
             *
            foreach (range(1, 3) as $i) {
                $table->string('file_path_'.$i);
                $table->string('file_name_'.$i);
                $table->integer('file_size_'.$i)->nullable();
                $table->string('mime_type_'.$i)->nullable();
            }*/
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->string('icon');
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
        Schema::dropIfExists('category');
    }
};
