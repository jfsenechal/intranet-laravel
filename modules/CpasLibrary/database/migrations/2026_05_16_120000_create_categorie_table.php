<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-cpas-library';

    public function up(): void
    {
        if (Schema::connection('maria-cpas-library')->hasTable('categorie')) {
            DB::connection('maria-cpas-library')
                ->table('categorie')
                ->whereNotNull('departments')
                ->whereRaw('JSON_VALID(departments) = 0')
                ->update(['departments' => null]);
            Schema::connection('maria-cpas-library')->table('categorie', function (Blueprint $table): void {
                $table->rename('categories');
            });
            Schema::connection('maria-cpas-library')->table('categories', function (Blueprint $table): void {
                $table->json('departments')->nullable()->change();
            });

            return;
        }
        Schema::connection('maria-cpas-library')->create('categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->string('slug', 255)->nullable()->unique();
            $table->string('icon', 255)->nullable();
            $table->string('color', 255)->nullable();
            $table->json('departments')->nullable();
            $table->boolean('public')->default(false);
            $table->json('users')->nullable();
            $table->index('parent_id');
        });
    }
};
