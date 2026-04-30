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
        if (Schema::connection('maria-agent')->hasTable('agent_applicationexterne')) {
            Schema::connection('maria-agent')->table('agent_applicationexterne', function (Blueprint $table): void {
                $table->rename('profile_external_application');
            });
            Schema::connection('maria-agent')->table('profile_external_application', function (Blueprint $table): void {
                $table->renameColumn('applicationexterne_id', 'external_application_id');
                $table->renameColumn('agent_id', 'profile_id');
            });
        } elseif (! Schema::connection('maria-agent')->hasTable('profile_external_application')) {
            Schema::connection('maria-agent')->create('profile_external_application', function (Blueprint $table): void {
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->foreignId('external_application_id')->constrained('external_applications')->cascadeOnDelete();

                $table->primary(['profile_id', 'external_application_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-agent')->dropIfExists('profile_external_application');
    }
};
