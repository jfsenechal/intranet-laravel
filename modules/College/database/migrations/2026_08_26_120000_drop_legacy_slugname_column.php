<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `college_recipients` was renamed in place from the legacy `destinataire` table, which carries a
 * `slugname` column nothing in the module ever reads or writes. The fresh-install branch of the
 * create migration copied it, so it exists on every database; drop it everywhere.
 */
return new class extends Migration
{
    protected $connection = 'maria-college';

    public function up(): void
    {
        if (! Schema::connection($this->connection)->hasColumn('college_recipients', 'slugname')) {
            return;
        }

        Schema::connection($this->connection)->table('college_recipients', function (Blueprint $table): void {
            $table->dropColumn('slugname');
        });
    }
};
