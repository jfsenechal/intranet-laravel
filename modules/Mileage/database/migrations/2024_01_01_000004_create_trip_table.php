<?php

use AcMarche\Mileage\Models\Declaration;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-mileage')->hasTable('deplacement')) {
            Schema::connection('maria-mileage')->table('deplacement', function (Blueprint $table) {
                $table->rename('trips');
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
                $table->softDeletes();
            });
        } else {
            Schema::connection('maria-mileage')->create('trips', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Declaration::class)->nullable()->constrained('declarations')->onDelete('set null');
                $table->foreignIdFor(User::class)->nullable()->constrained('users')->onDelete('set null');
                $table->integer('distance');
                $table->dateTime('departure_date');
                $table->dateTime('arrival_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->text('content');
                $table->decimal('rate', 10, 2)->nullable();
                $table->decimal('omnium', 10, 2)->nullable();
                $table->decimal('meal_expense', 10, 2)->nullable();
                $table->decimal('train_expense', 10, 2)->nullable();
                $table->string('type_movement');
                $table->string('departure_location')->nullable();
                $table->string('arrival_location')->nullable();
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
        Schema::connection('maria-mileage')->dropIfExists('trips');
    }
};
