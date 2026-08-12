<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-agent';

    public function up(): void
    {
        if ($this->hasUniqueEmployeeIndex()) {
            return;
        }

        Schema::connection('maria-agent')->table('profiles', function (Blueprint $table): void {
            $table->unique('employee_id');
        });
    }

    public function down(): void
    {
        if (! $this->hasUniqueEmployeeIndex()) {
            return;
        }

        Schema::connection('maria-agent')->table('profiles', function (Blueprint $table): void {
            $table->dropUnique(['employee_id']);
        });
    }

    private function hasUniqueEmployeeIndex(): bool
    {
        return collect(Schema::connection('maria-agent')->getIndexes('profiles'))
            ->contains(fn (array $index): bool => $index['unique'] && $index['columns'] === ['employee_id']);
    }
};
