<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('employe')) {
            Schema::connection($this->connection)->table('employe', function (Blueprint $table): void {
                $table->rename('employees');
            });
            Schema::connection($this->connection)->table('employees', function (Blueprint $table): void {
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('slugname', 'slug');
                $table->renameColumn('fonction', 'job_title');
                $table->renameColumn('birthday', 'birth_date');
                $table->renameColumn('email', 'private_email');
                $table->renameColumn('phone_private', 'private_phone');
                $table->renameColumn('mobile_private', 'private_mobile');
                $table->renameColumn('entree_le', 'hired_at');
                $table->renameColumn('sorti_le', 'left_at');
                $table->renameColumn('anciennete_pecuniaire', 'salary_seniority_date');
                $table->renameColumn('anciennete_echelle', 'scale_seniority_date');
                $table->renameColumn('date_rappel', 'reminder_date');
                $table->renameColumn('statut', 'status');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('urgence', 'emergency_contact');
                $table->renameColumn('image_name', 'photo');
                $table->renameColumn('adresse', 'address');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('ville', 'city');
                $table->renameColumn('registre_nationale', 'national_registry_number');
                $table->renameColumn('unite_locale', 'local_unit');
                $table->renameColumn('bareme_echelle', 'pay_scale_code');
                $table->renameColumn('indemnite', 'allowance');
                $table->renameColumn('civilite', 'civility');
                $table->renameColumn('mutuelle_affiliation', 'insurance_affiliation');
                $table->renameColumn('stagiaire_type', 'intern_type');
                $table->renameColumn('email_professionnel', 'professional_email');
                $table->renameColumn('mobile_professionnel', 'professional_mobile');
                $table->renameColumn('telephone_professionnel', 'professional_phone');
                $table->renameColumn('telephone_extension_professionnel', 'professional_phone_extension');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
                $table->renameColumn('archive', 'is_archived');
                $table->renameColumn('birthday_display', 'show_birthday');
                // Candidate fields
                $table->renameColumn('candi_date_reception', 'received_at');
                $table->renameColumn('candi_courrier_reference', 'mail_reference');
                $table->renameColumn('candi_niveau_diplome', 'diploma_level');
                $table->renameColumn('candi_nature_diplome', 'diploma_nature');
                $table->renameColumn('candiFileName', 'candidate_file_name');
                $table->renameColumn('candi_courrier_date_transmission', 'mail_sent_at');
                $table->renameColumn('candi_courrier_nombre', 'mail_count');
                $table->renameColumn('candi_prioritaire', 'priority');
                $table->renameColumn('candi_service_id', 'candidate_service_id');
                $table->removeColumn('employeur_save_id');
                $table->renameColumn('echelle_id', 'pay_scale_id');
                $table->renameColumn('prerequis_id', 'prerequisite_id');
                $table->renameColumn('mutuelle_id', 'health_insurance_id');
                $table->removeColumn('phone_office');
                $table->removeColumn('mobile_office');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('employees')) {
            Schema::connection($this->connection)->create('employees', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('uid', 100)->nullable()->comment('login');
                $table->string('username', 100)->nullable();
                $table->string('slug', 62);
                $table->string('civility', 100)->nullable();
                $table->string('last_name', 100);
                $table->string('first_name', 100);
                $table->string('job_title', 255)->nullable();
                $table->date('birth_date')->nullable();
                $table->boolean('show_birthday')->default(true);
                $table->string('emergency_contact')->nullable();
                $table->string('private_email', 255)->nullable();
                $table->string('private_phone', 150)->nullable();
                $table->string('private_mobile', 150)->nullable();
                $table->string('professional_email', 100)->nullable();
                $table->string('professional_mobile', 100)->nullable();
                $table->string('professional_phone', 100)->nullable();
                $table->string('professional_phone_extension', 100)->nullable();
                $table->string('address', 150)->nullable();
                $table->integer('postal_code')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('national_registry_number', 100)->nullable();
                $table->date('hired_at')->nullable();
                $table->date('left_at')->nullable();
                $table->date('salary_seniority_date')->nullable();
                $table->date('scale_seniority_date')->nullable();
                $table->date('reminder_date')->nullable();
                $table->string('status', 150)->nullable();
                $table->longText('notes')->nullable();
                $table->string('photo', 255)->nullable();
                $table->foreignId('pay_scale_id')->nullable();
                $table->string('pay_scale_code', 255)->nullable();
                $table->string('local_unit', 100)->nullable();
                $table->string('allowance', 200)->nullable();
                $table->foreignId('health_insurance_id')->nullable();
                $table->string('insurance_affiliation', 100)->nullable();
                $table->string('intern_type', 100)->nullable();
                $table->foreignId('prerequisite_id')->nullable();
                $table->boolean('is_archived')->default(false);
                // Candidate fields
                $table->date('received_at')->nullable();
                $table->longText('mail_reference')->nullable();
                $table->longText('diploma_level')->nullable();
                $table->string('diploma_nature', 200)->nullable();
                $table->string('candidate_file_name', 255)->nullable();
                $table->date('mail_sent_at')->nullable();
                $table->integer('mail_count')->default(0);
                $table->string('priority', 100)->nullable();
                $table->foreignId('candidate_service_id')->nullable();
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }
    }
};
