<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('incoming_mail_recipient')) {
            return;
        }

        Schema::connection('maria-courrier')->create('incoming_mail_recipient', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incoming_mail_id')->constrained('incoming_mails')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('recipients')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);

            $table->index(['incoming_mail_id', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('incoming_mail_recipient');
    }
};
