<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-agent';

    public function up(): void
    {
        if (Schema::connection('maria-agent')->hasTable('history')) {
            Schema::connection('maria-agent')->table('history', function (Blueprint $table): void {
                $table->rename('histories');
            });
            Schema::connection('maria-agent')->table('histories', function (Blueprint $table): void {
                $table->renameColumn('value_old', 'old_value');
                $table->renameColumn('value_new', 'new_value');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
                $table->renameColumn('agent_id', 'profile_id');
            });
        } elseif (! Schema::connection('maria-agent')->hasTable('histories')) {
            Schema::connection('maria-agent')->create('histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->string('name', 150);
                $table->json('old_value')->nullable();
                $table->json('new_value')->nullable();
                $table->string('username', 100);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-agent')->dropIfExists('histories');
    }
};
