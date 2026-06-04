<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-guichet';

    public function up(): void
    {
        if (Schema::connection('maria-guichet')->hasTable('office')) {
            Schema::connection('maria-guichet')->table('office', function (Blueprint $table): void {
                $table->rename('offices');
            });
        } else {
            Schema::connection('maria-guichet')->create('offices', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('service')->nullable();
            });
        }

        if (Schema::connection('maria-guichet')->hasTable('reason')) {
            Schema::connection('maria-guichet')->table('reason', function (Blueprint $table): void {
                $table->rename('reasons');
            });
        } else {
            Schema::connection('maria-guichet')->create('reasons', function (Blueprint $table): void {
                $table->id();
                $table->string('content');
            });
        }

        if (Schema::connection('maria-guichet')->hasTable('ticket')) {
            Schema::connection('maria-guichet')->table('ticket', function (Blueprint $table): void {
                $table->rename('tickets');
            });
        } else {
            Schema::connection('maria-guichet')->create('tickets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
                $table->string('number');
                $table->string('reason');
                $table->datetime('assigned_date')->nullable();
                $table->string('assigned_by')->nullable();
                $table->string('user_add');
                $table->datetime('createdAt')->nullable();
                $table->datetime('updatedAt')->nullable();
                $table->boolean('archive')->default(false);
                $table->string('service');
                $table->date('created_date')->storedAs('CAST(`createdAt` AS DATE)')->nullable();
            });

            Schema::connection('maria-guichet')->table('tickets', function (Blueprint $table): void {
                $table->unique(['number', 'created_date'], 'ticket_number_day_unique');
            });
        }

    }
};
