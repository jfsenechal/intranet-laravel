<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('contrat')) {
            Schema::connection($this->connection)->table('contrat', function (Blueprint $table) {
                $table->rename('contracts');
            });
            Schema::connection($this->connection)->table('contracts', function (Blueprint $table) {
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('remplacement', 'is_replacement');
                $table->renameColumn('cloture', 'is_closed');
                $table->renameColumn('avenant', 'is_amendment');
                $table->renameColumn('suspension', 'is_suspended');
                $table->renameColumn('fonction', 'job_title');
                $table->renameColumn('statut', 'status');
                $table->renameColumn('regime', 'work_regime');
                $table->renameColumn('regime_horaire', 'hourly_regime');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
                $table->renameColumn('remplace_id', 'replaces_id');
                $table->renameColumn('nature_id', 'contract_nature_id');
                $table->renameColumn('typecontrat_id', 'contract_type_id');
                $table->renameColumn('echelle_id', 'pay_scale_id');
                $table->renameColumn('file1Name', 'file1_name');
                $table->renameColumn('file2Name', 'file2_name');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('contracts')) {
            Schema::connection($this->connection)->create('contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id');
                $table->foreignId('employer_id');
                $table->foreignId('direction_id')->nullable();
                $table->foreignId('service_id')->nullable();
                $table->foreignId('contract_nature_id')->nullable();
                $table->foreignId('contract_type_id')->nullable();
                $table->foreignId('pay_scale_id')->nullable();
                $table->foreignId('replaces_id')->nullable()->comment('ID of employee being replaced');
                $table->longText('college')->nullable();
                $table->string('is_replacement', 5)->comment('oui ou non');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('reminder_date')->nullable();
                $table->boolean('is_closed')->default(false);
                $table->boolean('is_amendment')->default(false);
                $table->boolean('is_suspended')->nullable();
                $table->string('job_title', 250)->nullable();
                $table->string('status', 250)->nullable();
                $table->double('work_regime')->nullable();
                $table->string('hourly_regime', 255)->nullable();
                $table->string('file1_name', 255)->nullable();
                $table->string('file2_name', 255)->nullable();
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('contracts');
    }
};
