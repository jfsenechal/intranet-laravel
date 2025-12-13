<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('courrier')) {
            Schema::connection('maria-courrier')->table('courrier', function (Blueprint $table) {
                $table->rename('incoming_mails');
            });
            Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table) {
                $table->renameColumn('numero', 'reference_number');
                $table->renameColumn('expediteur', 'sender');
                $table->renameColumn('date_courrier', 'mail_date');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('notifie', 'is_notified');
                $table->renameColumn('recommande', 'is_registered');
                $table->renameColumn('accuse', 'has_acknowledgment');
                $table->integer('file_size')->nullable();
                $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
                $table->string('file_mime')->nullable();
                $table->softDeletes();
                $table->index('reference_number');
                $table->index('mail_date');
            });
        } else {
            Schema::connection('maria-courrier')->create('incoming_mails', function (Blueprint $table): void {
                $table->id();
                $table->string('reference_number');
                $table->string('sender');
                $table->longText('description')->nullable();
                $table->date('mail_date');
                $table->boolean('is_notified')->default(false);
                $table->boolean('is_registered')->default(false);
                $table->boolean('has_acknowledgment')->default(false);
                $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
                $table->string('user_add');
                $table->softDeletes();
                $table->timestamps();
                $table->index('reference_number');
                $table->index('mail_date');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('incoming_mails');
    }
};
