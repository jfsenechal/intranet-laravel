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
        if (Schema::hasTable('actions')) {
            return;
        }
        Schema::table('strategic_objectives', function (Blueprint $table): void {
            $table->enum('synergy', ActionSynergyEnum::toArray())->nullable(false)->default(ActionSynergyEnum::NO)->change();
        });

        Schema::table('operational_objectives', function (Blueprint $table): void {
            $table->enum('synergy', ActionSynergyEnum::toArray())->nullable(false)->default(ActionSynergyEnum::NO)->change();
        });
        Schema::table('actions', function (Blueprint $table): void {
            $table->enum('synergy', ActionSynergyEnum::toArray())->nullable(false)->default(ActionSynergyEnum::NO)->change();
        });
    }
};
