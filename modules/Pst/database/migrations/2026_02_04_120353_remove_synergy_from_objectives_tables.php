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
        foreach (['strategic_objectives', 'operational_objectives'] as $name) {
            if (! Schema::hasColumn($name, 'synergy')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table): void {
                $table->dropColumn('synergy');
            });
        }
    }
};
