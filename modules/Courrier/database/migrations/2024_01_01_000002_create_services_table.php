<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('service')) {
            Schema::connection('maria-courrier')->table('service', function (Blueprint $table): void {
                $table->rename('courrier_services');
            });
            Schema::connection('maria-courrier')->table('courrier_services', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                $table->removeColumn('actif');
                $table->enum('department', DepartmentCourrierEnum::toArray())
                    ->nullable();
            });
        } elseif (! Schema::connection('maria-courrier')->hasTable('courrier_services')) {
            Schema::connection('maria-courrier')->create('courrier_services', function (Blueprint $table): void {
                $table->id();
                $table->string('slugname', 70)->unique();
                $table->string('name');
                $table->string('initials')->nullable();
                $table->enum('department', DepartmentCourrierEnum::toArray())
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('courrier_services');
    }
};
