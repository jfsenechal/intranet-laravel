<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('services')
            && ! Schema::connection('maria-courrier')->hasTable('courrier_services')) {
            Schema::connection('maria-courrier')->rename('services', 'courrier_services');
        }
    }

    public function down(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('courrier_services')
            && ! Schema::connection('maria-courrier')->hasTable('services')) {
            Schema::connection('maria-courrier')->rename('courrier_services', 'services');
        }
    }
};
