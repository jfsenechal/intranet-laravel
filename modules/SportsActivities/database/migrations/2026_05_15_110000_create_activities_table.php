<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-rescam';

    public function up(): void
    {
        if (Schema::connection('maria-rescam')->hasTable('activite')) {
            Schema::connection('maria-rescam')->table('activite', function (Blueprint $table): void {
                $table->rename('sports_activities');
            });
            Schema::connection('maria-rescam')->table('sports_activities', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('archive', 'archived');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
            });
        } else {
            Schema::connection('maria-rescam')->create('sports_activities', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 255);
                $table->longText('description')->nullable();
                $table->boolean('archived')->default(false);
                $table->timestamps();
            });
        }
    }
};
