<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-pst';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('actions')) {
            return;
        }
        Schema::table('actions', function (Blueprint $table): void {
            $table->boolean('is_internal')->default(false);
        });
    }
};
