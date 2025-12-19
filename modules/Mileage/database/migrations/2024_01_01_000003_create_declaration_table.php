<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-mileage')->hasTable('declaration')) {
            Schema::connection('maria-mileage')->table('declaration', function (Blueprint $table) {
                $table->rename('declarations');
            });
            Schema::connection('maria-mileage')->table('declarations', function (Blueprint $table) {
                $table->renameColumn('plaque1', 'car_license_plate1');
                $table->renameColumn('plaque2', 'car_license_plate2');
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('rue', 'street');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('localite', 'city');
                $table->renameColumn('tarif', 'rate');
                $table->renameColumn('tarif_omnium', 'rate_omnium');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('type_deplacement', 'type_movement');
                $table->renameColumn('article_budgetaire', 'budget_article');
                $table->renameColumn('date_college', 'college_date');
                $table->softDeletes();
            });
        } else {
            Schema::connection('maria-mileage')->create('declarations', function (Blueprint $table) {
                $table->id();
                $table->boolean('omnium')->default(false);
                $table->string('iban');
                $table->string('car_license_plate1');
                $table->string('car_license_plate2')->nullable();
                $table->string('last_name');
                $table->string('first_name');
                $table->string('street');
                $table->string('postal_code');
                $table->string('city');
                $table->decimal('rate', 10, 2);
                $table->decimal('rate_omnium', 10, 2);
                $table->string('type_movement');
                $table->date('college_date')->nullable();
                $table->string('budget_article')->nullable(false);
                $table->string('departments')->nullable();
                $table->string('user_add');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->dropIfExists('declarations');
    }
};
