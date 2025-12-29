<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        // Stages/Internships
        if (Schema::connection($this->connection)->hasTable('stage')) {
            Schema::connection($this->connection)->table('stage', function (Blueprint $table) {
                $table->rename('internships');
            });
            Schema::connection($this->connection)->table('internships', function (Blueprint $table) {
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('internships')) {
            Schema::connection($this->connection)->create('internships', function (Blueprint $table) {
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
            Schema::connection($this->connection)->table('candidature', function (Blueprint $table) {
                $table->rename('applications');
            });
            Schema::connection($this->connection)->table('applications', function (Blueprint $table) {
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
        } elseif (! Schema::connection($this->connection)->hasTable('applications')) {
            Schema::connection($this->connection)->create('applications', function (Blueprint $table) {
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
            Schema::connection($this->connection)->table('echeance', function (Blueprint $table) {
                $table->rename('deadlines');
            });
            Schema::connection($this->connection)->table('deadlines', function (Blueprint $table) {
                $table->renameColumn('intitule', 'title');
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
            Schema::connection($this->connection)->create('deadlines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->foreignId('direction_id')->nullable();
                $table->foreignId('service_id')->nullable();
                $table->string('title', 250);
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
            Schema::connection($this->connection)->table('document', function (Blueprint $table) {
                $table->rename('hr_documents');
            });
            Schema::connection($this->connection)->table('hr_documents', function (Blueprint $table) {
                $table->renameColumn('intitule', 'title');
                $table->renameColumn('fileName', 'file_name');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('hr_documents')) {
            Schema::connection($this->connection)->create('hr_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->nullable();
                $table->string('title', 255);
                $table->string('file_name', 255);
                $table->string('mime', 255);
                $table->longText('notes')->nullable();
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }

        // Valorisations
        if (Schema::connection($this->connection)->hasTable('valorisation')) {
            Schema::connection($this->connection)->table('valorisation', function (Blueprint $table) {
                $table->rename('valorizations');
            });
            Schema::connection($this->connection)->table('valorizations', function (Blueprint $table) {
                $table->renameColumn('employeur', 'employer_name');
                $table->renameColumn('duree', 'duration');
                $table->renameColumn('contenu', 'content');
                $table->renameColumn('fileName', 'file_name');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('valorizations')) {
            Schema::connection($this->connection)->create('valorizations', function (Blueprint $table) {
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
            Schema::connection($this->connection)->table('teletravail', function (Blueprint $table) {
                $table->rename('teleworks');
            });
            Schema::connection($this->connection)->table('teleworks', function (Blueprint $table) {
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
            Schema::connection($this->connection)->create('teleworks', function (Blueprint $table) {
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
            Schema::connection($this->connection)->table('sms', function (Blueprint $table) {
                $table->renameColumn('numero', 'phone_number');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('date_send', 'sent_at');
                $table->renameColumn('date_rappel_other', 'other_reminder_date');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('sms')) {
            Schema::connection($this->connection)->create('sms', function (Blueprint $table) {
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
            Schema::connection($this->connection)->table('operateur', function (Blueprint $table) {
                $table->rename('operators');
            });
            Schema::connection($this->connection)->table('operators', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('slugname', 'slug');
                $table->renameColumn('contact_nom', 'contact_last_name');
                $table->renameColumn('contact_prenom', 'contact_first_name');
                $table->renameColumn('fonction', 'job_title');
                $table->renameColumn('telephone', 'phone');
                $table->renameColumn('adresse', 'address');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('ville', 'city');
                $table->renameColumn('objectifs', 'objectives');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('transmis_le', 'transmitted_at');
                $table->renameColumn('date_reception', 'received_at');
                $table->renameColumn('courrier_reference', 'mail_reference');
                $table->renameColumn('courrier_nombre', 'mail_count');
                $table->renameColumn('niveau_diplome', 'diploma_level');
                $table->renameColumn('nature_diplome', 'diploma_nature');
                $table->renameColumn('archive', 'is_archived');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('operators')) {
            Schema::connection($this->connection)->create('operators', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->string('name', 255);
                $table->string('slug', 62);
                $table->string('contact_last_name', 255)->nullable();
                $table->string('contact_first_name', 255)->nullable();
                $table->string('job_title', 255)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('phone', 255)->nullable();
                $table->string('gsm', 255)->nullable();
                $table->string('address', 255)->nullable();
                $table->integer('postal_code')->nullable();
                $table->string('city', 255)->nullable();
                $table->longText('objectives')->nullable();
                $table->longText('notes')->nullable();
                $table->date('transmitted_at')->nullable();
                $table->date('received_at')->nullable();
                $table->longText('mail_reference')->nullable();
                $table->integer('mail_count')->default(0);
                $table->longText('diploma_level')->nullable();
                $table->string('diploma_nature', 200)->nullable();
                $table->boolean('is_archived')->default(false);
                $table->string('user_add', 255);
                $table->timestamps();
            });
        }

        // Notifications
        if (Schema::connection($this->connection)->hasTable('notification')) {
            Schema::connection($this->connection)->table('notification', function (Blueprint $table) {
                $table->rename('hr_notifications');
            });
            Schema::connection($this->connection)->table('hr_notifications', function (Blueprint $table) {
                $table->renameColumn('intitule', 'title');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('hr_notifications')) {
            Schema::connection($this->connection)->create('hr_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('title', 250);
                $table->integer('object_id');
                $table->string('object_type', 100);
                $table->foreignId('employer_id')->nullable();
                $table->string('user_add', 255);
                $table->timestamps();
            });
        }

        // Notification Users
        if (! Schema::connection($this->connection)->hasTable('notification_users')) {
            Schema::connection($this->connection)->create('notification_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_id');
                $table->string('user', 255);
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('notification_users');
        Schema::connection($this->connection)->dropIfExists('hr_notifications');
        Schema::connection($this->connection)->dropIfExists('operators');
        Schema::connection($this->connection)->dropIfExists('sms');
        Schema::connection($this->connection)->dropIfExists('teleworks');
        Schema::connection($this->connection)->dropIfExists('valorizations');
        Schema::connection($this->connection)->dropIfExists('hr_documents');
        Schema::connection($this->connection)->dropIfExists('deadlines');
        Schema::connection($this->connection)->dropIfExists('applications');
        Schema::connection($this->connection)->dropIfExists('internships');
    }
};
