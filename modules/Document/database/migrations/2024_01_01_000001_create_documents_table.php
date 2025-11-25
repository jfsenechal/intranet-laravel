<?php


use AcMarche\Document\Models\Category;
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
        if (Schema::connection('maria-document')->hasTable('documents')) {
            return;
        }

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('content')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('category')->nullable();
            $table->string('user_add');
            $table->foreignIdFor(Category::class);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('categories');
    }
};
