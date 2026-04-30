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
        if (Schema::connection('maria-courrier')->hasTable('service_destinataire')) {
            Schema::connection('maria-courrier')->table('service_destinataire', function (Blueprint $table): void {
                $table->rename('recipient_service');
            });
            Schema::connection('maria-courrier')->table('recipient_service', function (Blueprint $table): void {
                $table->renameColumn('destinataire_id', 'recipient_id');
            });
        } elseif (! Schema::connection('maria-courrier')->hasTable('recipient_service')) {
            Schema::connection('maria-courrier')->create('recipient_service', function (Blueprint $table): void {
                $table->foreignId('recipient_id')->constrained('recipients')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('courrier_services')->cascadeOnDelete();
                $table->primary(['service_id', 'recipient_id']);
            });
        }
    }
};
