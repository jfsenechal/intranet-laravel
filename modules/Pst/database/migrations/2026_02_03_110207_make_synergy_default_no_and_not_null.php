<?php

declare(strict_types=1);

use AcMarche\Pst\Enums\ActionSynergyEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-pst';

    public function up(): void
    {
        foreach (['strategic_objectives', 'operational_objectives', 'actions'] as $name) {
            if (! Schema::hasColumn($name, 'synergy')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table): void {
                $table->enum('synergy', ActionSynergyEnum::toArray())->nullable(false)->default(ActionSynergyEnum::NO)->change();
            });
        }
    }
};
