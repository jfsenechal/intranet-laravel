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
        Schema::connection('maria-mileage')->table('tarif', function (Blueprint $table) {
            $table->rename('rates');
        });
        Schema::connection('maria-mileage')->table('declaration', function (Blueprint $table) {
            $table->rename('declarations');
        });
        Schema::connection('maria-mileage')->table('article_budgetaire', function (Blueprint $table) {
            $table->rename('budget_articles');
        });
        Schema::connection('maria-mileage')->table('deplacement', function (Blueprint $table) {
            $table->rename('trips');
        });

        Schema::connection('maria-mileage')->table('rates', function (Blueprint $table) {
            $table->renameColumn('montant', 'amount');
            $table->renameColumn('date_debut', 'start_date');
            $table->renameColumn('date_fin', 'end_date');
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
        });
        Schema::connection('maria-mileage')->table('budget_articles', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->renameColumn('fonctionnel', 'functional_code');
            $table->renameColumn('economique', 'economic_code');
            $table->renameColumn('departement', 'department');
            $table->renameColumn('created', 'created_at');
            $table->renameColumn('updated', 'updated_at');
        });
        Schema::connection('maria-mileage')->table('trips', function (Blueprint $table) {
            $table->renameColumn('distance', 'distance');
            $table->renameColumn('date_depart', 'departure_date');
            $table->renameColumn('tarif', 'rate');
            $table->renameColumn('user', 'user_add');
            $table->renameColumn('type_deplacement', 'type_movement');
            $table->renameColumn('lieu_depart', 'departure_location');
            $table->renameColumn('lieu_arrive', 'arrival_location');
            $table->renameColumn('date_arrive', 'arrival_date');
            $table->renameColumn('repas', 'meal_expense');
            $table->renameColumn('heure_start', 'start_time');
            $table->renameColumn('heure_end', 'end_time');
            $table->renameColumn('train', 'train_expense');
            $table->renameColumn('utilisateur_id', 'user_id');
            $table->renameColumn('created', 'created_at');
            $table->renameColumn('updated', 'updated_at');
        });
    }
};
