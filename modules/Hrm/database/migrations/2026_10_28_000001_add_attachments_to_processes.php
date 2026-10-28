<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        Schema::connection($this->connection)->table('processes', function (Blueprint $table): void {
            $table->json('attachments')->nullable()->after('description');
            $table->json('attachment_names')->nullable()->after('attachments');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('processes', function (Blueprint $table): void {
            $table->dropColumn(['attachments', 'attachment_names']);
        });
    }
};
