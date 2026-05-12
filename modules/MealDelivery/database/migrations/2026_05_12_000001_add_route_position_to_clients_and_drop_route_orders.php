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

        if (! Schema::connection($connection)->hasColumn('clients', 'route_position')) {
            Schema::connection($connection)->table('clients', function (Blueprint $table): void {
                $table->integer('route_position')->nullable()->after('route_id');
            });
        }

        if (Schema::connection($connection)->hasTable('route_orders')) {
            DB::connection($connection)
                ->table('route_orders')
                ->orderBy('client_id')
                ->each(function (object $row) use ($connection): void {
                    DB::connection($connection)
                        ->table('clients')
                        ->where('id', $row->client_id)
                        ->update(['route_position' => $row->position]);
                });

            Schema::connection($connection)->drop('route_orders');
        }
    }

    public function down(): void
    {
        $connection = 'maria-meal-delivery';

        if (! Schema::connection($connection)->hasTable('route_orders')) {
            Schema::connection($connection)->create('route_orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('route_id')->constrained('delivery_routes')->cascadeOnDelete();
                $table->foreignId('client_id')->unique()->constrained('clients')->cascadeOnDelete();
                $table->integer('position')->nullable();
                $table->timestamps();
            });
        }

        DB::connection($connection)
            ->table('clients')
            ->whereNotNull('route_position')
            ->whereNotNull('route_id')
            ->orderBy('id')
            ->each(function (object $client) use ($connection): void {
                DB::connection($connection)->table('route_orders')->insert([
                    'route_id' => $client->route_id,
                    'client_id' => $client->id,
                    'position' => $client->route_position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        if (Schema::connection($connection)->hasColumn('clients', 'route_position')) {
            Schema::connection($connection)->table('clients', function (Blueprint $table): void {
                $table->dropColumn('route_position');
            });
        }
    }
};
