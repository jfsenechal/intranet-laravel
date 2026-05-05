<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('client')) {
            Schema::connection('maria-meal-delivery')->table('client', function (Blueprint $table): void {
                $table->rename('clients');
            });
            Schema::connection('maria-meal-delivery')->table('clients', function (Blueprint $table): void {
                $table->renameColumn('civilite', 'salutation');
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('slugname', 'slug');
                $table->renameColumn('rue', 'street');
                $table->renameColumn('numero', 'number');
                $table->renameColumn('code_postal', 'postal_code');
                $table->renameColumn('localite', 'city');
                $table->renameColumn('etage', 'floor');
                $table->renameColumn('telephone', 'phone');
                $table->renameColumn('ne_le', 'birth_date');
                $table->renameColumn('contact_nom', 'contact_name');
                $table->renameColumn('contact_telephone', 'contact_phone');
                $table->renameColumn('contact_remarque', 'contact_notes');
                $table->renameColumn('remarque', 'notes');
                $table->renameColumn('commande_recurrente', 'recurring_order');
                $table->renameColumn('tournee_id', 'route_id');
                $table->renameColumn('tournee_save', 'route_backup');
                $table->renameColumn('is_actif', 'is_active');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('clients')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('salutation')->nullable();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('slug', 70)->unique();
            $table->string('street');
            $table->string('number');
            $table->integer('postal_code');
            $table->string('city');
            $table->string('floor')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_notes')->nullable();
            $table->text('notes')->nullable();
            $table->text('recurring_order')->nullable();
            $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->text('route_backup')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('use_cafeteria')->default(false);
            $table->timestamps();
        });
    }
};
