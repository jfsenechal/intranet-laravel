<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('formation')) {
            Schema::connection($this->connection)->table('formation', function (Blueprint $table) {
                $table->rename('trainings');
            });
            Schema::connection($this->connection)->table('trainings', function (Blueprint $table) {
                $table->renameColumn('intitule', 'title');
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
                $table->renameColumn('date_college', 'college_date');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('duree', 'duration_hours');
                $table->renameColumn('typef', 'training_type');
                $table->renameColumn('attestation_name', 'certificate_file');
                $table->renameColumn('attestation_recue', 'certificate_received');
                $table->renameColumn('attestation_recue_le', 'certificate_received_at');
                $table->renameColumn('accorde_par', 'granted_by');
                $table->renameColumn('accorde_le', 'granted_at');
                $table->renameColumn('cloture', 'is_closed');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('trainings')) {
            Schema::connection($this->connection)->create('trainings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id');
                $table->string('title', 150);
                $table->longText('description')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('college_date')->nullable();
                $table->date('reminder_date')->nullable();
                $table->integer('duration_hours')->nullable();
                $table->string('training_type', 50)->comment('type 1, type 2, type 3');
                $table->string('certificate_file', 255)->nullable();
                $table->boolean('certificate_received')->default(false);
                $table->date('certificate_received_at')->nullable();
                $table->string('granted_by', 255)->nullable();
                $table->date('granted_at')->nullable();
                $table->boolean('is_closed')->nullable();
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('trainings');
    }
};
