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
        if (Schema::connection('maria-rescam')->hasTable('sportif')) {
            Schema::connection('maria-rescam')->table('sportif', function (Blueprint $table): void {
                $table->rename('sports_members');
            });
            Schema::connection('maria-rescam')->table('sports_members', function (Blueprint $table): void {
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('ne_le', 'birth_date');
                $table->renameColumn('rue', 'street');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('localite', 'city');
                $table->renameColumn('telephone', 'phone');
                $table->renameColumn('gsm', 'mobile');
                $table->removeColumn('user');
                $table->renameColumn('remarque', 'comment');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
            });
        } elseif (!Schema::connection('maria-rescam')->hasTable('sports_members')) {
            Schema::connection('maria-rescam')->create('sports_members', function (Blueprint $table): void {
                $table->id();
                $table->string('last_name', 255);
                $table->string('first_name', 255);
                $table->date('birth_date')->nullable();
                $table->string('street', 255);
                $table->string('postal_code', 255);
                $table->string('city', 255);
                $table->string('phone', 255)->nullable();
                $table->string('mobile', 255)->nullable();
                $table->string('email', 255)->nullable();
                $table->longText('comment')->nullable();
                $table->timestamps();
            });
        }
    }
};
