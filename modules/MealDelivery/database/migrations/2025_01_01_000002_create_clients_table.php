<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = 'maria-meal-delivery';

        if (Schema::connection($connection)->hasTable('client')) {
            Schema::connection($connection)->table('client', function (Blueprint $table): void {
                $table->rename('clients');
            });
            Schema::connection($connection)->table('clients', function (Blueprint $table): void {
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
                $table->removeColumn('tournee_save');
                $table->renameColumn('is_actif', 'is_active');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
            });
        }

        if (! Schema::connection($connection)->hasTable('clients')) {
            Schema::connection($connection)->create('clients', function (Blueprint $table): void {
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
                $table->foreignId('route_id')->constrained('delivery_routes');
                $table->boolean('is_active')->default(true);
                $table->boolean('use_cafeteria')->default(false);
                $table->timestamps();
            });

            return;
        }

        $this->normalizeRouteRelationship($connection);
    }

    private function normalizeRouteRelationship(string $connection): void
    {
        $this->dropForeignKeyIfExists($connection, 'clients', 'route_id');

        $routeIdType = $this->columnType($connection, 'delivery_routes', 'id');
        if ($routeIdType !== null && mb_stripos($routeIdType, 'bigint') === false) {
            $this->dropForeignKeyIfExists($connection, 'tournee_ordre', 'tournee_id');
            $this->dropForeignKeyIfExists($connection, 'route_orders', 'route_id');

            Schema::connection($connection)->table('delivery_routes', function (Blueprint $table): void {
                $table->bigIncrements('id')->change();
            });
        }

        Schema::connection($connection)->table('clients', function (Blueprint $table): void {
            $table->unsignedBigInteger('route_id')->nullable(false)->change();
            $table->foreign('route_id')->references('id')->on('delivery_routes');
        });
    }

    private function columnType(string $connection, string $table, string $column): ?string
    {
        $row = DB::connection($connection)->selectOne(
            'SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column],
        );

        return $row?->COLUMN_TYPE;
    }

    private function dropForeignKeyIfExists(string $connection, string $table, string $column): void
    {
        if (! Schema::connection($connection)->hasTable($table)) {
            return;
        }

        $constraint = DB::connection($connection)->selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1',
            [$table, $column],
        );

        if ($constraint === null) {
            return;
        }

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) use ($constraint): void {
            $blueprint->dropForeign($constraint->CONSTRAINT_NAME);
        });
    }
};
