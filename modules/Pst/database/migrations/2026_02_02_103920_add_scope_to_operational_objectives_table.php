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
        Schema::table('operational_objectives', function (Blueprint $table): void {
            $table->string('scope')->after('department')->default(ActionScopeEnum::EXTERNAL->value);
        });

        DB::table('operational_objectives')
            ->where('department', 'COMMUN')
            ->update(['scope' => ActionScopeEnum::INTERNAL->value]);

        DB::table('operational_objectives')
            ->where('department', '!=', 'COMMUN')
            ->update(['scope' => ActionScopeEnum::EXTERNAL->value]);
    }
};
