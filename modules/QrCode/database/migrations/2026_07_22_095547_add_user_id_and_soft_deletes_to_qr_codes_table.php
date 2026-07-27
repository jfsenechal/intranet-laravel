<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->integer('user_id')->nullable()->after('id')->index();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
            $table->dropSoftDeletes();
        });
    }
};
