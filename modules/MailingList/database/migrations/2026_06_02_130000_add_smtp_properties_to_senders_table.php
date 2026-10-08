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
            $table->string('smtp_host')->nullable()->after('logo');
            $table->unsignedSmallInteger('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->text('smtp_password')->nullable()->after('smtp_username');
        });
    }

    public function down(): void
    {
        Schema::table('senders', function (Blueprint $table): void {
            $table->dropColumn(['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password']);
        });
    }
};
