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
        Schema::connection($this->connection)->table('sms_reminders', function (Blueprint $table): void {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('sms_reminders', function (Blueprint $table): void {
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();
        });
    }
};
