<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('service_destinataire')) {
            Schema::connection('maria-courrier')->table('service_destinataire', function (Blueprint $table) {
                $table->rename('recipient_service');
            });
            Schema::connection('maria-courrier')->table('recipient_service', function (Blueprint $table) {
                $table->renameColumn('destinataire_id', 'recipient_id');
            });
        } else {
            Schema::connection('maria-courrier')->create('recipient_service', function (Blueprint $table): void {
                $table->foreignId('recipient_id')->constrained('recipients')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->primary(['service_id', 'recipient_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('recipient_service');
    }
};
