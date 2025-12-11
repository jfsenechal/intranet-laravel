<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('incoming_mails')) {
            return;
        }

        Schema::create('incoming_mails', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->string('sender_name');
            $table->text('sender_address')->nullable();
            $table->date('received_date');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->integer('attachment_size')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->string('assigned_to')->nullable();
            $table->date('processed_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('user_add');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_mails');
    }
};
