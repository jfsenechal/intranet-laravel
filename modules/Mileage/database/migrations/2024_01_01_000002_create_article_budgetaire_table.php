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
        if (Schema::connection('maria-mileage')->hasTable('article_budgetaire')) {
            Schema::connection('maria-mileage')->table('article_budgetaire', function (Blueprint $table) {
                $table->rename('budget_articles');
            });
            Schema::connection('maria-mileage')->table('budget_articles', function (Blueprint $table) {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('fonctionnel', 'functional_code');
                $table->renameColumn('economique', 'economic_code');
                $table->renameColumn('departement', 'department');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->softDeletes();
            });
        } else {
            Schema::connection('maria-mileage')->create('budget_articles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('functional_code');
                $table->string('economic_code');
                $table->string('department');
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
        Schema::connection('maria-mileage')->dropIfExists('budget_articles');
    }
};
