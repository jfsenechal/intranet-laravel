<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-pst';

    public function up(): void
    {
        if (! Schema::hasTable('services') || Schema::hasTable('pst_services')) {
            return;
        }

        Schema::rename('services', 'pst_services');
    }

    public function down(): void
    {
        if (! Schema::hasTable('pst_services') || Schema::hasTable('services')) {
            return;
        }

        Schema::rename('pst_services', 'services');
    }
};
