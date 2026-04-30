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
        if (Schema::connection('maria-agent')->hasTable('agent_materiel')) {
            Schema::connection('maria-agent')->table('agent_materiel', function (Blueprint $table): void {
                $table->rename('profile_hardware');
            });
            Schema::connection('maria-agent')->table('profile_hardware', function (Blueprint $table): void {
                $table->renameColumn('pc_existant', 'existing_pc');
                $table->renameColumn('pc_nouveau', 'new_pc');
                $table->renameColumn('autre', 'other');
                $table->renameColumn('agent_id', 'profile_id');
                if (! Schema::connection('maria-agent')->hasColumn('profile_hardware', 'created_at')) {
                    $table->timestamps();
                }
            });
        } elseif (! Schema::connection('maria-agent')->hasTable('profile_hardware')) {
            Schema::connection('maria-agent')->create('profile_hardware', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->string('existing_pc')->nullable();
                $table->string('new_pc')->nullable();
                $table->longText('other')->nullable();
                $table->boolean('vpn')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-agent')->dropIfExists('profile_hardware');
    }
};
