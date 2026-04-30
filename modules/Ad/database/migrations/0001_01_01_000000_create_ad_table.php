<?php

declare(strict_types=1);

use AcMarche\Ad\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-ad';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-ad')->hasTable('classified_ads')) {
            return;
        }

        Schema::create('classified_ads', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->string('end_date');
            $table->text('content');
            $table->string('user_add')->nullable();
            $table->boolean('archive')->default(false);
            $table->boolean('resolved')->default(false);
            $table->boolean('sent')->default(false);
            $table->json('medias')->nullable();
            $table->timestamps();
            $table->foreignIdFor(Category::class);
        });

        Schema::create('ad_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps(false);
        });
    }
};
