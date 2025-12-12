<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('services')) {
            return;
        }

        Schema::connection('maria-courrier')->create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 70)->unique();
            $table->string('name');
            $table->string('initials')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('services');
    }
};
