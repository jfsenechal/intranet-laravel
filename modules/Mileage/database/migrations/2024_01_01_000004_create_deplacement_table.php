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
            return;
        }
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->dropIfExists('trips');
    }
};
