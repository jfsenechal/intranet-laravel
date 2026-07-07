<?php

declare(strict_types=1);

use AcMarche\News\Enums\DepartmentEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Map of legacy integer department values to the new enum values.
     *
     * @var array<int, string>
     */
    private const LEGACY_MAP = [
        1 => DepartmentEnum::COMMON->value,
        2 => DepartmentEnum::VILLE->value,
        3 => DepartmentEnum::CPAS->value,
    ];

    protected $connection = 'maria-news';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::LEGACY_MAP as $legacy => $value) {
            DB::connection($this->connection)
                ->table('news')
                ->where('department', (string) $legacy)
                ->update(['department' => $value]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::LEGACY_MAP as $legacy => $value) {
            DB::connection($this->connection)
                ->table('news')
                ->where('department', $value)
                ->update(['department' => (string) $legacy]);
        }
    }
};
