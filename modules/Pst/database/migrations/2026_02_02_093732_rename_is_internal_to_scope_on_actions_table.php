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
        Schema::table('actions', function (Blueprint $table): void {
            $table->string('scope')->default(ActionScopeEnum::INTERNAL->value)->after('is_internal');
        });

        DB::table('actions')->where('is_internal', true)->update(['scope' => ActionScopeEnum::INTERNAL->value]);
        DB::table('actions')->where('is_internal', false)->update(['scope' => ActionScopeEnum::EXTERNAL->value]);

        Schema::table('actions', function (Blueprint $table): void {
            $table->dropColumn('is_internal');
        });
    }
};
