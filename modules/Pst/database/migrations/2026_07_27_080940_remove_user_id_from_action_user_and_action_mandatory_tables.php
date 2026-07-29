<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-pst';

    /**
     * @var list<string>
     */
    private array $tables = ['action_user', 'action_mandatory'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::connection($this->getConnection())->hasColumn($table, 'user_id')) {
                continue;
            }

            if (Schema::connection($this->getConnection())->hasIndex($table, $table.'_action_id_user_id_unique')) {
                Schema::connection($this->getConnection())->table($table, function (Blueprint $blueprint) use ($table): void {
                    $blueprint->dropUnique($table.'_action_id_user_id_unique');
                });
            }

            Schema::connection($this->getConnection())->table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('user_id');
            });
        }
    }

    /**
     * The user_id values are not restored: these tables are keyed on username
     * since the pst:migration command ran.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::connection($this->getConnection())->hasColumn($table, 'user_id')) {
                continue;
            }

            Schema::connection($this->getConnection())->table($table, function (Blueprint $blueprint): void {
                $blueprint->unsignedBigInteger('user_id')->nullable()->after('action_id');
            });
        }
    }
};
