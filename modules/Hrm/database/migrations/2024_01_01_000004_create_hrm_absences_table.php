<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('absence')) {
            Schema::connection($this->connection)->table('absence', function (Blueprint $table) {
                $table->rename('absences');
            });
            Schema::connection($this->connection)->table('absences', function (Blueprint $table) {
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('date_cloture', 'closed_date');
                $table->renameColumn('reprise', 'has_resumed');
                $table->renameColumn('raison', 'reason');
                $table->renameColumn('cloture', 'is_closed');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
                $table->renameColumn('pointeuse', 'clock_updated');
                $table->renameColumn('encare', 'encore');
                $table->renameColumn('dossier_agent', 'agent_file');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('absences')) {
            Schema::connection($this->connection)->create('absences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('reminder_date')->nullable();
                $table->date('closed_date')->nullable();
                $table->string('has_resumed', 5)->nullable()->comment('oui ou non');
                $table->longText('notes');
                $table->string('ssa', 5)->nullable()->comment('reason code');
                $table->string('reason', 255)->nullable();
                $table->string('clock_updated', 5)->nullable()->comment('oui ou non');
                $table->string('encore', 5)->nullable()->comment('oui ou non');
                $table->boolean('is_closed')->default(false);
                $table->string('acropole', 5)->nullable()->comment('oui ou non');
                $table->string('agent_file', 5)->nullable()->comment('oui ou non');
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('absences');
    }
};
