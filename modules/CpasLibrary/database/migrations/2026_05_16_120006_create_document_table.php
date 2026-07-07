<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-cpas-library';

    public function up(): void
    {
        if (Schema::connection('maria-cpas-library')->hasTable('document')) {
            Schema::connection('maria-cpas-library')->table('document', function (Blueprint $table): void {
                $table->rename('documents');
            });

            return;
        }
        if (Schema::connection('maria-cpas-library')->hasTable('documents')) {
            return;
        }
        Schema::connection('maria-cpas-library')->create('documents', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->dateTime('createdAt')->nullable();
            $table->dateTime('updatedAt')->nullable();
        });
    }
};
