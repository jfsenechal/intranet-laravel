<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('signatures', 'email_service')) {
            return;
        }

        Schema::table('signatures', function (Blueprint $table): void {
            $table->string('email_service')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('signatures', 'email_service')) {
            return;
        }

        Schema::table('signatures', function (Blueprint $table): void {
            $table->dropColumn('email_service');
        });
    }
};
