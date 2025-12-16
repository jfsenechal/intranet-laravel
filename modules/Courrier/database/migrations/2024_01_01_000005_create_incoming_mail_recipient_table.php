<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('courrier_destinataire')) {
            Schema::connection('maria-courrier')->table('courrier_destinataire', function (Blueprint $table) {
                $table->rename('incoming_mail_recipient');
            });
            Schema::connection('maria-courrier')->table('incoming_mail_recipient', function (Blueprint $table) {
                $table->renameColumn('principal', 'is_primary');
                $table->renameColumn('courrier_id', 'incoming_mail_id');
                $table->renameColumn('destinataire_id', 'recipient_id');
                //   $table->foreignId('incoming_mail_id')->constrained('incoming_mails')->cascadeOnDelete();
                //   $table->foreignId('recipient_id')->constrained('recipients')->cascadeOnDelete();

                $table->index(['incoming_mail_id', 'recipient_id']);
            });

        } else {
            Schema::connection('maria-courrier')->create('incoming_mail_recipient', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('incoming_mail_id')->constrained('incoming_mails')->cascadeOnDelete();
                $table->foreignId('recipient_id')->constrained('recipients')->cascadeOnDelete();
                $table->boolean('is_primary')->default(false);

                $table->index(['incoming_mail_id', 'recipient_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('incoming_mail_recipient');
    }
};
