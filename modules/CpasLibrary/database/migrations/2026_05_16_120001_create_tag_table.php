<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-cpas-library';

    public function up(): void
    {
        if (Schema::connection('maria-cpas-library')->hasTable('tag')) {
            Schema::connection('maria-cpas-library')->table('tag', function (Blueprint $table): void {
                $table->rename('tags');
            });

            return;
        }
        Schema::connection('maria-cpas-library')->create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->nullable();
        });
    }
};
