<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-mailing-list';

    public function up(): void
    {
        Schema::table('senders', function (Blueprint $table): void {
            $table->foreignId('signature_id')
                ->nullable()
                ->after('footer')
                ->constrained('signatures')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('senders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('signature_id');
        });
    }
};
