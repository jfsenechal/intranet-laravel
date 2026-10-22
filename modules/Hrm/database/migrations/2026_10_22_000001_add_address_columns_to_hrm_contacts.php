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
        Schema::connection($this->connection)->table('hrm_contacts', function (Blueprint $table): void {
            $table->string('street')->nullable()->after('phone_2');
            $table->string('postal_code', 10)->nullable()->after('street');
            $table->string('city', 100)->nullable()->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('hrm_contacts', function (Blueprint $table): void {
            $table->dropColumn(['street', 'postal_code', 'city']);
        });
    }
};
