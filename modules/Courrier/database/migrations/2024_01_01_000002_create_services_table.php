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
            Schema::connection('maria-courrier')->table('service', function (Blueprint $table) {
                $table->rename('services');
            });
            Schema::connection('maria-courrier')->table('services', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('actif', 'is_active');
                $table->enum('department', DepartmentCourrierEnum::toArray())
                    ->nullable();
            });
        } else {
            Schema::connection('maria-courrier')->create('services', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 70)->unique();
                $table->string('name');
                $table->string('initials')->nullable();
                $table->boolean('is_active')->default(true);
                $table->enum('department', DepartmentCourrierEnum::toArray())
                    ->nullable();
            });
        }

    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('services');
    }
};
