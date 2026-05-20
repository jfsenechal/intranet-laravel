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
        $schema = Schema::connection('maria-rescam');
        $schema->dropIfExists('inscriptions');
        $schema->dropIfExists('groupes');
        $schema->dropIfExists('sportifs');
        $schema->dropIfExists('activites');

        if (Schema::connection('maria-rescam')->hasTable('activities')) {
            return;
        }

        Schema::connection('maria-rescam')->create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->longText('description')->nullable();
            $table->string('user', 255);
            $table->boolean('archived')->default(false);
            $table->timestamps();
        });
    }
};
