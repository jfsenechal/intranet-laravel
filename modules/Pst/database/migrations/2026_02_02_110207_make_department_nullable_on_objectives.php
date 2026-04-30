<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-pst';

    public function up(): void
    {
        if (Schema::hasTable('actions')) {
            return;
        }
        Schema::table('strategic_objectives', function (Blueprint $table): void {
            $table->string('department')->nullable()->change();
        });

        Schema::table('operational_objectives', function (Blueprint $table): void {
            $table->string('department')->nullable()->change();
        });
    }
};
