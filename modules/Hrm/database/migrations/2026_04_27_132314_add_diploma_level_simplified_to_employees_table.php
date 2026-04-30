<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        Schema::connection($this->connection)->table('employees', function (Blueprint $table): void {
            $table->longText('diploma_level_simplified')->nullable()->after('diploma_level');
        });

        DB::connection($this->connection)
            ->table('employees')
            ->where('status', StatusEnum::INTERN->value)
            ->whereNotNull('diploma_level')
            ->update([
                'diploma_level_simplified' => DB::raw('diploma_level'),
                'diploma_level' => null,
            ]);
    }

    public function down(): void
    {
        DB::connection($this->connection)
            ->table('employees')
            ->where('status', StatusEnum::INTERN->value)
            ->whereNotNull('diploma_level_simplified')
            ->update([
                'diploma_level' => DB::raw('diploma_level_simplified'),
            ]);

        Schema::connection($this->connection)->table('employees', function (Blueprint $table): void {
            $table->dropColumn('diploma_level_simplified');
        });
    }
};
