<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('modules', 'droit_multiple')) {
            Schema::table('modules', function (Blueprint $table): void {
                $table->renameColumn('droit_multiple', 'allow_multiple_roles');
            });
        } elseif (! Schema::hasColumn('modules', 'allow_multiple_roles')) {
            Schema::table('modules', function (Blueprint $table): void {
                $table->boolean('allow_multiple_roles')->default(false);
            });
        }
    }
};
