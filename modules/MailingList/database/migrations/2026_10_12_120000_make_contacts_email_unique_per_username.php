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
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropUnique(['email']);
            $table->unique(['email', 'username']);
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropUnique(['email', 'username']);
            $table->unique('email');
        });
    }
};
