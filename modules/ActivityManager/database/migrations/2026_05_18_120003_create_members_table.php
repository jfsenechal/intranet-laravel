<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    protected $connection = 'maria-activity-manager';

    public function up(): void
    {
        if (Schema::connection('maria-activity-manager')->hasTable('membre')) {
            Schema::connection('maria-activity-manager')->table('membre', function (Blueprint $table): void {
                $table->rename('members');
            });
            Schema::connection('maria-activity-manager')->table('members', function (Blueprint $table): void {
                $table->renameColumn('civilite', 'civility');
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('rue', 'street');
                $table->renameColumn('numero', 'number');
                $table->renameColumn('codepostal', 'postal_code');
                $table->renameColumn('localite', 'city');
                $table->renameColumn('gsm', 'mobile');
                $table->renameColumn('telephone', 'phone');
                $table->renameColumn('remarque', 'remark');
                $table->renameColumn('inscrit_le', 'registered_at');
            });

            return;
        }
        Schema::connection('maria-activity-manager')->create('members', function (Blueprint $table): void {
            $table->id();
            $table->string('civility', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('first_name', 50);
            $table->string('street', 150)->nullable();
            $table->string('number', 50)->nullable();
            $table->integer('postal_code')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->boolean('enabled')->default(true);
            $table->longText('remark')->nullable();
            $table->date('registered_at')->nullable();
        });
    }
};
