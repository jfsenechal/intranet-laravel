<?php

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('expediteur')) {
            Schema::connection('maria-courrier')->table('expediteur', function (Blueprint $table) {
                $table->rename('senders');
            });
            Schema::connection('maria-courrier')->table('senders', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('slugname', 'slug');
                $table->enum('department', DepartmentCourrierEnum::toArray())
                    ->nullable();
            });
        } else {
            Schema::connection('maria-courrier')->create('senders', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 70)->unique();
                $table->string('name');
                $table->enum('department', DepartmentCourrierEnum::toArray())
                    ->nullable();
            });
        }

    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('senders');
    }
};
