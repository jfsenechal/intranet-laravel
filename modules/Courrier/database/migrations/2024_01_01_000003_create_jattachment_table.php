<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('attachement')) {
            Schema::connection('maria-courrier')->table('attachement', function (Blueprint $table): void {
                $table->rename('attachments');
            });
            Schema::connection('maria-courrier')->table('attachments', function (Blueprint $table): void {
                $table->renameColumn('updatedAt', 'updated_at');
                $table->renameColumn('courrier_id', 'incoming_mail_id');
            });

        } elseif (! Schema::connection('maria-courrier')->hasTable('attachments')) {
            Schema::connection('maria-courrier')->create('attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('incoming_mail_id')->constrained('incoming_mails')->cascadeOnDelete();
                $table->string('file_name');
                $table->string('mime');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('attachments');
    }
};
