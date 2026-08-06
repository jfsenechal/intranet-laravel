<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the obsolete `modules.icon` column.
     *
     * It held FontAwesome classes from the pre-Laravel intranet (`fa fa-coffee
     * icon-green`). Nothing reads it: every view renders its own hardcoded
     * Heroicon. `color`, which the module tiles still use as a background, is
     * left in place.
     */
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table): void {
            $table->dropColumn('icon');
        });
    }

    /**
     * Recreate the column. The FontAwesome classes it held are not restored.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table): void {
            $table->string('icon')->nullable();
        });
    }
};
