<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-pst';

    public function up(): void
    {
        if (Schema::hasTable('media') && ! Schema::hasTable('pst_media')) {
            Schema::rename('media', 'pst_media');
        }

        if (Schema::hasTable('histories') && ! Schema::hasTable('pst_histories')) {
            Schema::rename('histories', 'pst_histories');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pst_media') && ! Schema::hasTable('media')) {
            Schema::rename('pst_media', 'media');
        }

        if (Schema::hasTable('pst_histories') && ! Schema::hasTable('histories')) {
            Schema::rename('pst_histories', 'histories');
        }
    }
};
