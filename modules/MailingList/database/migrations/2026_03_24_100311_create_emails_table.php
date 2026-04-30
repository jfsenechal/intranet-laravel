<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-mailing-list';

    public function up(): void
    {
        if (Schema::connection('maria-mailing-list')->hasTable('emails')) {
            return;
        }
        Schema::create('emails', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->index();
            $table->foreignId('sender_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->longText('body');
            $table->json('attachments')->nullable();
            $table->string('status')->default('draft');
            $table->string('batch_id')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('total_count')->default(0);
            $table->timestamps();
        });
    }
};
