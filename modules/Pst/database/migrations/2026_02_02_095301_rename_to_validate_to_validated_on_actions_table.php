<?php

declare(strict_types=1);

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
            $table->boolean('validated')->default(false)->after('to_validate');
        });

        // Invert the logic: to_validate=true means not validated, to_validate=false means validated
        DB::table('actions')->where('to_validate', true)->update(['validated' => false]);
        DB::table('actions')->where('to_validate', false)->update(['validated' => true]);

        Schema::table('actions', function (Blueprint $table): void {
            $table->dropColumn('to_validate');
        });
    }
};
