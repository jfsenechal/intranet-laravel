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
        if (Schema::connection('maria-agent')->hasTable('agent_dossier')) {
            Schema::connection('maria-agent')->table('agent_dossier', function (Blueprint $table): void {
                $table->rename('profile_folder');
            });
            Schema::connection('maria-agent')->table('profile_folder', function (Blueprint $table): void {
                $table->renameColumn('dossier_id', 'folder_id');
                $table->renameColumn('agent_id', 'profile_id');
            });
        } elseif (! Schema::connection('maria-agent')->hasTable('profile_folder')) {
            Schema::connection('maria-agent')->create('profile_folder', function (Blueprint $table): void {
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->foreignId('folder_id')->constrained('folders')->cascadeOnDelete();

                $table->primary(['profile_id', 'folder_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-agent')->dropIfExists('profile_folder');
    }
};
