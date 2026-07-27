<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stamped one second after make_department_nullable_on_objectives so it deterministically
 * runs last: the two files shared a timestamp, and the alphabetical tie-break put
 * "not_nullable" first, leaving a freshly migrated database nullable.
 */
return new class() extends Migration
{
    protected $connection = 'maria-pst';

    public function up(): void
    {
        Schema::table('strategic_objectives', function (Blueprint $table): void {
            $table->string('department')->nullable(false)->change();
        });

        Schema::table('operational_objectives', function (Blueprint $table): void {
            $table->string('department')->nullable(false)->change();
        });
    }
};
