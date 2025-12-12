<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('incoming_mail_service')) {
            return;
        }

        Schema::connection('maria-courrier')->create('incoming_mail_service', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incoming_mail_id')->constrained('incoming_mails')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->boolean('is_primary')->default(true);

            $table->index(['incoming_mail_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('incoming_mail_service');
    }
};
