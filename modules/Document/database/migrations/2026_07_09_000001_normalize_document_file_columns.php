<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-document';

    /**
     * Run the migrations.
     *
     * The unused `file_path` column becomes nullable, and `file_name` is
     * normalised to hold the full disk-relative path (e.g. "documents/foo.pdf")
     * so it matches what Filament's FileUpload stores for new records.
     */
    public function up(): void
    {
        Schema::connection('maria-document')->table('documents', function (Blueprint $table): void {
            $table->string('file_path')->nullable()->change();
        });

        $directory = config('document.storage.directory');

        DB::connection('maria-document')
            ->table('documents')
            ->whereNotNull('file_name')
            ->where('file_name', '<>', '')
            ->where('file_name', 'not like', '%/%')
            ->update([
                'file_name' => DB::raw("CONCAT('".$directory."/', file_name)"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $directory = config('document.storage.directory');

        DB::connection('maria-document')
            ->table('documents')
            ->where('file_name', 'like', $directory.'/%')
            ->update([
                'file_name' => DB::raw('SUBSTRING(file_name, '.(mb_strlen((string) $directory) + 2).')'),
            ]);
    }
};
