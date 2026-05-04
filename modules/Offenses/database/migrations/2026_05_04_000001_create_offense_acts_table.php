<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-offenses')->hasTable('acte')) {
            Schema::connection('maria-offenses')->table('acte', function (Blueprint $table): void {
                $table->rename('offense_acts');
            });
            Schema::connection('maria-offenses')->table('offense_acts', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } else {
            Schema::connection('maria-offenses')->create('offense_acts', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('user_add')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-offenses')->dropIfExists('offense_acts');
    }
};
