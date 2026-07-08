<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-aldermen-agenda';

    public function up(): void
    {
        Schema::connection('maria-aldermen-agenda')->table('events', function (Blueprint $table): void {
            if (Schema::connection('maria-aldermen-agenda')->hasColumn('events', 'slugname')) {
                $table->removeColumn('slugname');
            }

            if (Schema::connection('maria-aldermen-agenda')->hasColumn('events', 'object')) {
                $table->renameColumn('object', 'description');
            }
        });
    }
};
