<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('recipients')) {
            return;
        }

        Schema::connection('maria-courrier')->create('recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supervisor_id')->nullable()->constrained('recipients')->nullOnDelete();
            $table->string('slug', 70)->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('username');
            $table->string('email');
            $table->boolean('is_active')->default(true);
            $table->boolean('receives_attachments')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('recipients');
    }
};
