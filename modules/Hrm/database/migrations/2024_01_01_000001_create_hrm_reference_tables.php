<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        // Employers table
        if (Schema::connection($this->connection)->hasTable('employeur')) {
            Schema::connection($this->connection)->table('employeur', function (Blueprint $table) {
                $table->rename('employers');
            });
            Schema::connection($this->connection)->table('employers', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('slugname', 'slug');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('employers')) {
            Schema::connection($this->connection)->create('employers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->string('slug', 75);
                $table->foreignId('parent_id')->nullable()->constrained('employers');
                $table->timestamps();
            });
        }

        // Directions table
        if (Schema::connection($this->connection)->hasTable('direction')) {
            Schema::connection($this->connection)->table('direction', function (Blueprint $table) {
                $table->rename('directions');
            });
            Schema::connection($this->connection)->table('directions', function (Blueprint $table) {
                $table->renameColumn('intitule', 'title');
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('slugname', 'slug');
                $table->renameColumn('directeur', 'director');
                $table->renameColumn('abreviation', 'abbreviation');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('directions')) {
            Schema::connection($this->connection)->create('directions', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100);
                $table->string('slug', 80);
                $table->string('director', 255)->nullable();
                $table->string('abbreviation', 255)->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->string('user_add', 255);
                $table->timestamps();
            });
        }

        // Services table
        if (Schema::connection($this->connection)->hasTable('service')) {
            Schema::connection($this->connection)->table('service', function (Blueprint $table) {
                $table->rename('services');
            });
            Schema::connection($this->connection)->table('services', function (Blueprint $table) {
                $table->renameColumn('intitule', 'title');
                $table->renameColumn('slugname', 'slug');
                $table->renameColumn('abreviation', 'abbreviation');
                $table->renameColumn('adresse', 'address');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('ville', 'city');
                $table->renameColumn('telephone', 'phone');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('services')) {
            Schema::connection($this->connection)->create('services', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100);
                $table->string('slug', 80);
                $table->string('abbreviation', 255)->nullable();
                $table->foreignId('direction_id')->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->string('address', 100)->nullable();
                $table->integer('postal_code')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('phone', 150)->nullable();
                $table->string('gsm', 150)->nullable();
                $table->longText('notes')->nullable();
                $table->string('user_add', 255);
                $table->timestamps();
            });
        }

        // Pay scales table
        if (Schema::connection($this->connection)->hasTable('echelle')) {
            Schema::connection($this->connection)->table('echelle', function (Blueprint $table) {
                $table->rename('pay_scales');
            });
            Schema::connection($this->connection)->table('pay_scales', function (Blueprint $table) {
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('intitule', 'title');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('pay_scales')) {
            Schema::connection($this->connection)->create('pay_scales', function (Blueprint $table) {
                $table->id();
                $table->string('title', 50);
                $table->longText('description')->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->timestamps();
            });
        }

        // Functions table
        if (Schema::connection($this->connection)->hasTable('fonction')) {
            Schema::connection($this->connection)->table('fonction', function (Blueprint $table) {
                $table->rename('job_functions');
            });
            Schema::connection($this->connection)->table('job_functions', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('job_functions')) {
            Schema::connection($this->connection)->create('job_functions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->timestamps();
            });
        }

        // Contract types table
        if (Schema::connection($this->connection)->hasTable('type_contrat')) {
            Schema::connection($this->connection)->table('type_contrat', function (Blueprint $table) {
                $table->rename('contract_types');
            });
            Schema::connection($this->connection)->table('contract_types', function (Blueprint $table) {
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('nom', 'name');
                $table->renameColumn('slugname', 'slug');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('contract_types')) {
            Schema::connection($this->connection)->create('contract_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->string('slug', 75);
                $table->string('description', 255)->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->timestamps();
            });
        }

        // Contract natures table
        if (Schema::connection($this->connection)->hasTable('nature_contrat')) {
            Schema::connection($this->connection)->table('nature_contrat', function (Blueprint $table) {
                $table->rename('contract_natures');
            });
            Schema::connection($this->connection)->table('contract_natures', function (Blueprint $table) {
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('nom', 'name');
                $table->renameColumn('slugname', 'slug');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('contract_natures')) {
            Schema::connection($this->connection)->create('contract_natures', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->string('slug', 75);
                $table->string('description', 255)->nullable();
                $table->foreignId('employer_id')->nullable();
                $table->timestamps();
            });
        }

        // Health insurance (Mutuelle) table
        if (Schema::connection($this->connection)->hasTable('mutuelle')) {
            Schema::connection($this->connection)->table('mutuelle', function (Blueprint $table) {
                $table->rename('health_insurances');
            });
            Schema::connection($this->connection)->table('health_insurances', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('health_insurances')) {
            Schema::connection($this->connection)->create('health_insurances', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->timestamps();
            });
        }

        // Public holidays table
        if (Schema::connection($this->connection)->hasTable('jour_ferie')) {
            Schema::connection($this->connection)->table('jour_ferie', function (Blueprint $table) {
                $table->rename('public_holidays');
            });
            Schema::connection($this->connection)->table('public_holidays', function (Blueprint $table) {
                $table->renameColumn('intitule', 'title');
                $table->renameColumn('date_jour', 'holiday_date');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('public_holidays')) {
            Schema::connection($this->connection)->create('public_holidays', function (Blueprint $table) {
                $table->id();
                $table->string('title', 250)->nullable();
                $table->date('holiday_date');
                $table->timestamps();
            });
        }

        // Prerequisites table
        if (Schema::connection($this->connection)->hasTable('prerequis')) {
            Schema::connection($this->connection)->table('prerequis', function (Blueprint $table) {
                $table->rename('prerequisites');
            });
            Schema::connection($this->connection)->table('prerequisites', function (Blueprint $table) {
                $table->renameColumn('employeur_id', 'employer_id');
                $table->renameColumn('intitule', 'title');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('prerequisites')) {
            Schema::connection($this->connection)->create('prerequisites', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100);
                $table->string('profession', 100)->nullable();
                $table->longText('description')->nullable();
                $table->string('user', 255);
                $table->foreignId('employer_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('prerequisites');
        Schema::connection($this->connection)->dropIfExists('public_holidays');
        Schema::connection($this->connection)->dropIfExists('health_insurances');
        Schema::connection($this->connection)->dropIfExists('contract_natures');
        Schema::connection($this->connection)->dropIfExists('contract_types');
        Schema::connection($this->connection)->dropIfExists('job_functions');
        Schema::connection($this->connection)->dropIfExists('pay_scales');
        Schema::connection($this->connection)->dropIfExists('services');
        Schema::connection($this->connection)->dropIfExists('directions');
        Schema::connection($this->connection)->dropIfExists('employers');
    }
};
