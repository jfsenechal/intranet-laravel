<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('incoming_mails')) {
            return;
        }

        Schema::connection('maria-courrier')->create('incoming_mails', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number');
            $table->string('sender');
            $table->longText('description')->nullable();
            $table->date('mail_date');
            $table->boolean('is_notified')->default(false);
            $table->boolean('is_registered')->default(false);
            $table->boolean('has_acknowledgment')->default(false);
            $table->string('user_add');
            $table->softDeletes();
            $table->timestamps();

            $table->index('reference_number');
            $table->index('mail_date');
        });
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('incoming_mails');
    }
};
