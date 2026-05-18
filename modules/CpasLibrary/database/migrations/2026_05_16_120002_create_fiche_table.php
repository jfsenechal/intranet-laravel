<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-cpas-library';

    public function up(): void
    {
        if (Schema::connection('maria-cpas-library')->hasTable('fiche')) {
            return;
        }
        Schema::connection('maria-cpas-library')->create('fiche', function (Blueprint $table): void {
            $table->id();
            $table->integer('category_id')->nullable();
            $table->string('type', 255)->nullable();
            $table->string('source', 255)->nullable();
            $table->date('date_promulgation')->nullable();
            $table->date('date_publication')->nullable();
            $table->string('name', 255);
            $table->longText('description')->nullable();
            $table->string('userAdd', 255);
            $table->string('mimeType', 255)->nullable();
            $table->dateTime('createdAt')->nullable();
            $table->dateTime('updatedAt')->nullable();
            $table->string('fileName', 190)->nullable();
            $table->integer('fileSize')->nullable();
            $table->string('slug', 255)->nullable();
            $table->date('date_rappel')->nullable();
            $table->string('type_document', 255)->nullable();
            $table->date('date_begin')->nullable();
            $table->date('date_end')->nullable();

            $table->foreign('category_id')
                ->references('id')->on('categorie')
                ->nullOnDelete();
            $table->index('category_id');
        });
    }
};
