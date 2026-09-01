<?php

declare(strict_types=1);

use AcMarche\App\Enums\SignatureEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renaming a case of SignatureEnum leaves rows holding a file name the enum no
     * longer knows, and the cast on Signature::$logo then throws on every read.
     * Fall those rows back to the commune logo. An empty logo means no logo was
     * ever chosen, so it is left alone.
     */
    public function up(): void
    {
        if (! Schema::hasTable('signatures')) {
            return;
        }

        DB::table('signatures')
            ->whereNotNull('logo')
            ->where('logo', '!=', '')
            ->whereNotIn('logo', array_column(SignatureEnum::cases(), 'value'))
            ->update(['logo' => SignatureEnum::MARCHE->value]);
    }

    public function down(): void
    {
        // The replaced file names are not recorded anywhere, so there is nothing to restore.
    }
};
