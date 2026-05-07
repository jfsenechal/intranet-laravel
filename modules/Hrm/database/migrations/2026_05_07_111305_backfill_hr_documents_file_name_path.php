<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        $directory = (string) config('hrm.uploads.documents');

        DB::connection($this->connection)
            ->table('hr_documents')
            ->whereNotNull('file_name')
            ->where('file_name', '!=', '')
            ->where('file_name', 'NOT LIKE', $directory.'/%')
            ->update([
                'file_name' => DB::raw("CONCAT('".addslashes($directory)."/', file_name)"),
            ]);
    }

};
