<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('dates_cours')) {
            return;
        }
        Schema::connection('maria-activity-manager')->create('dates_cours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cours_id')
                ->constrained('cours')
                ->cascadeOnDelete();
            $table->longText('remarque')->nullable();
            $table->dateTime('jour');
        });
    }
};
