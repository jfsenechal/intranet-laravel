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
        Schema::connection('maria-publication')->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('wpCategoryId');
        });

        Schema::connection('maria-publication')->create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('url');
            $table->dateTime('expire_date')->nullable();
            $table->string('user_add');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-publication')->dropIfExists('publication');
        Schema::connection('maria-publication')->dropIfExists('category');
    }
};
