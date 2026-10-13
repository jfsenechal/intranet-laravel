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
        Schema::table('emails', function (Blueprint $table): void {
            $table->json('address_book_ids')->nullable()->after('attachments');
            $table->json('contact_ids')->nullable()->after('address_book_ids');
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table): void {
            $table->dropColumn(['address_book_ids', 'contact_ids']);
        });
    }
};
