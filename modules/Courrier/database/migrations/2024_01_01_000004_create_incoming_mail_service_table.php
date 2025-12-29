<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('courrier_service')) {
            Schema::connection('maria-courrier')->table('courrier_service', function (Blueprint $table) {
                $table->rename('incoming_mail_service');
            });
            Schema::connection('maria-courrier')->table('incoming_mail_service', function (Blueprint $table) {
                $table->renameColumn('courrier_id', 'incoming_mail_id');
                $table->renameColumn('principal', 'is_primary');
                // $table->foreignId('incoming_mail_id')->constrained('incoming_mails')->cascadeOnDelete();
                // $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->index(['incoming_mail_id', 'service_id']);
            });

        } else {
            Schema::connection('maria-courrier')->create('incoming_mail_service', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('incoming_mail_id')->constrained('incoming_mails')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->boolean('is_primary')->default(true);

                $table->index(['incoming_mail_id', 'service_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('incoming_mail_service');
    }
};
