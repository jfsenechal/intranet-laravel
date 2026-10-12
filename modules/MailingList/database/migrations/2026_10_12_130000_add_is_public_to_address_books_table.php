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
        Schema::table('address_books', function (Blueprint $table): void {
            $table->boolean('is_public')->default(false)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('address_books', function (Blueprint $table): void {
            $table->dropColumn('is_public');
        });
    }
};
