<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        // Stages/Internships
        if (Schema::connection($this->connection)->hasTable('stage')) {
            Schema::connection($this->connection)->table('stage', function (Blueprint $table): void {
                $table->rename('internships');
            });
            Schema::connection($this->connection)->table('internships', function (Blueprint $table): void {
                $table->renameColumn('employe_id', 'employee_id');
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
            $this->dropForeignKeysOnColumn('internships', 'employee_id');
            Schema::connection($this->connection)->table('internships', function (Blueprint $table): void {
                $table->unsignedBigInteger('employee_id')->nullable(false)->change();
            });
        } elseif (! Schema::connection($this->connection)->hasTable('internships')) {
            Schema::connection($this->connection)->create('internships', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('employee_id');
                $table->foreignId('employer_id')->nullable();
                $table->foreignId('direction_id')->nullable();
                $table->foreignId('service_id')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->date('reminder_date')->nullable();
                $table->longText('notes')->nullable();
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // Candidatures
        if (Schema::connection($this->connection)->hasTable('candidature')) {
            Schema::connection($this->connection)->table('candidature', function (Blueprint $table): void {
                $table->rename('applications');
            });
            Schema::connection($this->connection)->table('applications', function (Blueprint $table): void {
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('employe_id', 'employee_id');
                $table->renameColumn('date_reception', 'received_at');
                $table->renameColumn('courrier_reference', 'mail_reference');
                $table->renameColumn('appel_public', 'public_call');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('file_name', 'file');
                $table->renameColumn('spontanee', 'is_spontaneous');
                $table->renameColumn('is_appel_public', 'is_public_call');
                $table->renameColumn('prioritaire', 'is_priority');
                $table->renameColumn('fonction_id', 'job_function_id');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
            $this->dropForeignKeysOnColumn('applications', 'employee_id');
            Schema::connection($this->connection)->table('applications', function (Blueprint $table): void {
                $table->unsignedBigInteger('employee_id')->nullable(false)->change();
            });
        } elseif (! Schema::connection($this->connection)->hasTable('applications')) {
            Schema::connection($this->connection)->create('applications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('employee_id');
                $table->foreignId('employer_id')->nullable();
                $table->foreignId('job_function_id')->nullable();
                $table->date('received_at');
                $table->longText('mail_reference')->nullable();
                $table->string('public_call', 150)->nullable();
                $table->longText('notes')->nullable();
                $table->string('file', 255)->nullable();
                $table->boolean('is_spontaneous')->nullable();
                $table->boolean('is_public_call')->nullable();
                $table->boolean('is_priority')->nullable();
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // Deadlines (Echeances)
        if (Schema::connection($this->connection)->hasTable('echeance')) {
            Schema::connection($this->connection)->table('echeance', function (Blueprint $table): void {
                $table->rename('deadlines');
            });
            Schema::connection($this->connection)->table('deadlines', function (Blueprint $table): void {
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('employe_id', 'employee_id');
                $table->renameColumn('intitule', 'name');
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('date_cloture', 'closed_date');
                $table->renameColumn('cloture', 'is_closed');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('deadlines')) {
            Schema::connection($this->connection)->create('deadlines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('employee_id')->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->foreignId('direction_id')->nullable();
                $table->foreignId('service_id')->nullable();
                $table->string('name', 250);
                $table->longText('note')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('reminder_date')->nullable();
                $table->date('closed_date')->nullable();
                $table->boolean('is_closed')->default(false);
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // HR Documents
        if (Schema::connection($this->connection)->hasTable('document')) {
            Schema::connection($this->connection)->table('document', function (Blueprint $table): void {
                $table->rename('hr_documents');
            });
            Schema::connection($this->connection)->table('hr_documents', function (Blueprint $table): void {
                $table->renameColumn('employe_id', 'employee_id');
                $table->renameColumn('intitule', 'name');
                $table->renameColumn('fileName', 'file_name');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
            $this->dropForeignKeysOnColumn('hr_documents', 'employee_id');
            Schema::connection($this->connection)->table('hr_documents', function (Blueprint $table): void {
                $table->unsignedBigInteger('employee_id')->nullable(false)->change();
            });
        } elseif (! Schema::connection($this->connection)->hasTable('hr_documents')) {
            Schema::connection($this->connection)->create('hr_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('employee_id')->nullable();
                $table->string('name', 255);
                $table->string('file_name', 255);
                $table->string('mime', 255);
                $table->longText('notes')->nullable();
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // Valorisations
        if (Schema::connection($this->connection)->hasTable('valorisation')) {
            Schema::connection($this->connection)->table('valorisation', function (Blueprint $table): void {
                $table->rename('valorizations');
            });
            Schema::connection($this->connection)->table('valorizations', function (Blueprint $table): void {
                $table->renameColumn('employe_id', 'employee_id');
                $table->renameColumn('employeur', 'employer_name');
                $table->renameColumn('duree', 'duration');
                $table->renameColumn('contenu', 'content');
                $table->renameColumn('fileName', 'file_name');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
            $this->dropForeignKeysOnColumn('valorizations', 'employee_id');
            Schema::connection($this->connection)->table('valorizations', function (Blueprint $table): void {
                $table->unsignedBigInteger('employee_id')->nullable(false)->change();
            });
        } elseif (! Schema::connection($this->connection)->hasTable('valorizations')) {
            Schema::connection($this->connection)->create('valorizations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('employee_id')->nullable();
                $table->string('employer_name', 150);
                $table->string('duration', 150);
                $table->string('regime', 150)->nullable();
                $table->longText('content')->nullable();
                $table->string('file_name', 255)->nullable();
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // Telework
        if (Schema::connection($this->connection)->hasTable('teletravail')) {
            Schema::connection($this->connection)->table('teletravail', function (Blueprint $table): void {
                $table->rename('teleworks');
            });
            Schema::connection($this->connection)->table('teleworks', function (Blueprint $table): void {
                $table->renameColumn('accord_reglement', 'regulation_agreement');
                $table->renameColumn('rue', 'street');
                $table->renameColumn('localite', 'locality');
                $table->renameColumn('validation_chef', 'manager_validated');
                $table->renameColumn('date_validation_chef', 'manager_validated_at');
                $table->renameColumn('remarque_validation_chef', 'manager_validation_notes');
                $table->renameColumn('remarque_grh', 'hr_notes');
                $table->renameColumn('remarque_user', 'employee_notes');
                $table->renameColumn('lieux', 'location_type');
                $table->renameColumn('jour', 'day_type');
                $table->renameColumn('jour_fixe', 'fixed_day');
                $table->renameColumn('jour_variable_motivation', 'variable_day_reason');
                $table->renameColumn('accord_informatique', 'it_agreement');
                $table->renameColumn('nom_validation_chef', 'manager_validator_name');
                $table->renameColumn('nom_validation_grh', 'hr_validator_name');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('teleworks')) {
            Schema::connection($this->connection)->create('teleworks', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->boolean('regulation_agreement');
                $table->boolean('it_agreement');
                $table->string('street', 120)->nullable();
                $table->string('postal_code', 10)->nullable();
                $table->string('locality', 120)->nullable();
                $table->smallInteger('location_type');
                $table->smallInteger('day_type');
                $table->smallInteger('fixed_day')->nullable();
                $table->longText('variable_day_reason')->nullable();
                $table->boolean('manager_validated')->nullable();
                $table->date('manager_validated_at')->nullable();
                $table->longText('manager_validation_notes')->nullable();
                $table->date('date_college')->nullable();
                $table->longText('hr_notes')->nullable();
                $table->longText('employee_notes')->nullable();
                $table->string('manager_validator_name', 100)->nullable();
                $table->string('hr_validator_name', 100)->nullable();
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // SMS
        if (Schema::connection($this->connection)->hasTable('sms')) {
            Schema::connection($this->connection)->table('sms', function (Blueprint $table): void {
                $table->rename('sms_reminders');
            });
            Schema::connection($this->connection)->table('sms_reminders', function (Blueprint $table): void {
                $table->renameColumn('employe_id', 'employee_id');
                $table->renameColumn('numero', 'phone_number');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('date_send', 'sent_at');
                $table->renameColumn('date_rappel_other', 'other_reminder_date');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
            $this->dropForeignKeysOnColumn('sms_reminders', 'employee_id');
            Schema::connection($this->connection)->table('sms_reminders', function (Blueprint $table): void {
                $table->unsignedBigInteger('employee_id')->nullable(false)->change();
            });
        } elseif (! Schema::connection($this->connection)->hasTable('sms_reminders')) {
            Schema::connection($this->connection)->create('sms_reminders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('employee_id')->nullable();
                $table->string('phone_number', 12);
                $table->string('message', 220);
                $table->date('reminder_date');
                $table->date('other_reminder_date')->nullable();
                $table->date('sent_at')->nullable();
                $table->string('result', 255)->nullable();
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // Operators
        if (Schema::connection($this->connection)->hasTable('operateur')) {
            Schema::connection($this->connection)->table('operateur', function (Blueprint $table): void {
                $table->drop('operators');
            });
        }

        // Notifications
        if (Schema::connection($this->connection)->hasTable('notification')) {
            Schema::connection($this->connection)->table('notification', function (Blueprint $table): void {
                $table->rename('hr_notifications');
            });
            Schema::connection($this->connection)->table('hr_notifications', function (Blueprint $table): void {
                $table->renameColumn('intitule', 'name');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('hr_notifications')) {
            Schema::connection($this->connection)->create('hr_notifications', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 250);
                $table->integer('object_id');
                $table->string('object_type', 100);
                $table->foreignId('employer_id')->nullable();
                $table->string('user_add', 255);
                $table->timestamps();
            });
        }

        // Notification Users
        if (! Schema::connection($this->connection)->hasTable('notification_users')) {
            Schema::connection($this->connection)->create('notification_users', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('notification_id');
                $table->string('user', 255);
            });
        }
    }

    private function dropForeignKeysOnColumn(string $table, string $column): void
    {
        $foreignKeys = DB::connection($this->connection)->select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        foreach ($foreignKeys as $fk) {
            Schema::connection($this->connection)->table($table, function (Blueprint $blueprint) use ($fk): void {
                $blueprint->dropForeign($fk->CONSTRAINT_NAME);
            });
        }
    }
};
