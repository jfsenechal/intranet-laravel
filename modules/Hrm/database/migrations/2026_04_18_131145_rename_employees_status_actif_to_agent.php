<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        DB::connection($this->connection)
            ->table('employees')
            ->where('status', 'Actif')
            ->update(['status' => 'Agent']);
    }

    public function down(): void
    {
        DB::connection($this->connection)
            ->table('employees')
            ->where('status', 'Agent')
            ->update(['status' => 'Actif']);
    }
};
