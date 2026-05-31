<?php

declare(strict_types=1);

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
            $table->timestamp('is_new_hire_updated_at')->nullable()->after('is_new_hire');
        });

        DB::connection($this->connection)
            ->table('employees')
            ->where('is_new_hire', true)
            ->whereNotNull('hired_at')
            ->update([
                'is_new_hire_updated_at' => DB::raw('hired_at'),
            ]);
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('employees', function (Blueprint $table): void {
            $table->dropColumn('is_new_hire_updated_at');
        });
    }
};
