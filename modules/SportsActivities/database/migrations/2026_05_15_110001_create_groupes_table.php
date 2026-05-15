<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-rescam';

    public function up(): void
    {
        if (Schema::connection('maria-rescam')->hasTable('groupes')) {
            return;
        }
        Schema::connection('maria-rescam')->create('groupes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activite_id')
                ->constrained('activites');
            $table->string('jour', 255);
            $table->string('heure', 255);
            $table->string('lieux', 255);
            $table->string('age', 255);
            $table->double('prix')->default(0);
            $table->longText('description')->nullable();
            $table->longText('remarque')->nullable();
            $table->string('user', 255);
            $table->timestamps();
        });
    }
};
