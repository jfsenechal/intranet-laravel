<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-mediation')->hasTable('accord_type')) {
            Schema::connection('maria-mediation')->table('accord_type', function (Blueprint $table): void {
                $table->rename('agreement_types');
            });
            Schema::connection('maria-mediation')->table('agreement_types', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
                $table->renameColumn('slugname', 'slug');
            });
        } elseif (! Schema::connection('maria-mediation')->hasTable('agreement_types')) {
            Schema::connection('maria-mediation')->create('agreement_types', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug', 70)->unique();
                $table->timestamps();
            });
        }
    }
};
