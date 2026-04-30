<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-agent';

    public function up(): void
    {
        if (Schema::connection('maria-agent')->hasTable('application_externe')) {
            Schema::connection('maria-agent')->table('application_externe', function (Blueprint $table): void {
                $table->rename('external_applications');
            });
            Schema::connection('maria-agent')->table('external_applications', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                if (! Schema::connection('maria-agent')->hasColumn('external_applications', 'created_at')) {
                    $table->timestamps();
                }
            });
        } elseif (! Schema::connection('maria-agent')->hasTable('external_applications')) {
            Schema::connection('maria-agent')->create('external_applications', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->longText('description')->nullable();
                $table->unsignedInteger('service_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-agent')->dropIfExists('external_applications');
    }
};
