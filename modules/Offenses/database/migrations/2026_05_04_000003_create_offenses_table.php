<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-offenses')->hasTable('incivilite')) {
            Schema::connection('maria-offenses')->table('incivilite', function (Blueprint $table): void {
                $table->rename('offenses');
            });
            Schema::connection('maria-offenses')->table('offenses', function (Blueprint $table): void {
                $table->renameColumn('contrevenant_id', 'offender_id');
                $table->renameColumn('acte_id', 'offense_act_id');
                $table->renameColumn('date_decision', 'decision_date');
                $table->renameColumn('amende', 'fine_amount');
                $table->renameColumn('avis_procureur', 'prosecutor_opinion');
                $table->renameColumn('fileName', 'file_name');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection('maria-offenses')->hasTable('offenses')) {
            Schema::connection('maria-offenses')->create('offenses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('offender_id')->nullable()->constrained('offenders')->nullOnDelete();
                $table->foreignId('offense_act_id')->nullable()->constrained('offense_acts')->nullOnDelete();
                $table->date('decision_date')->nullable();
                $table->double('fine_amount')->nullable();
                $table->boolean('mediation')->default(false);
                $table->string('prosecutor_opinion')->nullable();
                $table->string('file_name')->nullable();
                $table->string('user_add')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-offenses')->dropIfExists('offenses');
    }
};
