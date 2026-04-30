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
        if (Schema::connection('maria-agent')->hasTable('dossier')) {
            Schema::connection('maria-agent')->table('dossier', function (Blueprint $table): void {
                $table->rename('folders');
            });
            Schema::connection('maria-agent')->table('folders', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                if (! Schema::connection('maria-agent')->hasColumn('folders', 'created_at')) {
                    $table->timestamps();
                }
            });
        } elseif (! Schema::connection('maria-agent')->hasTable('folders')) {
            Schema::connection('maria-agent')->create('folders', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('name');
                $table->longText('description')->nullable();
                $table->timestamps();

                $table->foreign('parent_id')->references('id')->on('folders')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-agent')->dropIfExists('folders');
    }
};
