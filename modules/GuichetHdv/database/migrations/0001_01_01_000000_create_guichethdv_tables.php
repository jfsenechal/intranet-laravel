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
            return;
        }

        Schema::connection('maria-guichet')->create('office', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('service')->nullable();
        });

        Schema::connection('maria-guichet')->create('reason', function (Blueprint $table): void {
            $table->id();
            $table->string('content');
        });

        Schema::connection('maria-guichet')->create('ticket', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained('office')->nullOnDelete();
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

        Schema::connection('maria-guichet')->table('ticket', function (Blueprint $table): void {
            $table->unique(['number', 'created_date'], 'ticket_number_day_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('maria-guichet')->dropIfExists('ticket');
        Schema::connection('maria-guichet')->dropIfExists('reason');
        Schema::connection('maria-guichet')->dropIfExists('office');
    }
};
