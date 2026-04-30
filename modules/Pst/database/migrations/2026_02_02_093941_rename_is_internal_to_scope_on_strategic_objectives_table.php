<?php

declare(strict_types=1);

use AcMarche\Pst\Enums\ActionScopeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->string('scope')->default(ActionScopeEnum::EXTERNAL->value)->after('is_internal');
        });

        DB::table('strategic_objectives')->where('is_internal', true)->update(['scope' => ActionScopeEnum::INTERNAL->value]);
        DB::table('strategic_objectives')->where('is_internal', false)->update(['scope' => ActionScopeEnum::EXTERNAL->value]);

        Schema::table('strategic_objectives', function (Blueprint $table): void {
            $table->dropColumn('is_internal');
        });
    }
};
