<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-telecommunication';

    public function up(): void
    {
        $schema = Schema::connection('maria-telecommunication');

        if ($schema->hasTable('telephones')) {
            if ($schema->hasColumn('telephones', 'slugname')) {
                $schema->table('telephones', function (Blueprint $table): void {
                    $table->renameColumn('ligne_id', 'line_type_id');
                    $table->renameColumn('slugname', 'slug');
                    $table->renameColumn('utilisateur', 'user_name');
                    $table->renameColumn('numero', 'number');
                    $table->renameColumn('archive', 'archived');
                    $table->renameColumn('departement', 'department');
                    $table->renameColumn('art_budg', 'budget_article');
                    $table->renameColumn('localisation', 'location');
                    $table->renameColumn('cout_fixe', 'fixed_cost');
                    $table->renameColumn('created', 'created_at');
                    $table->renameColumn('updated', 'updated_at');
                });
            }

            return;
        }

        $schema->create('telephones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('line_type_id')
                ->nullable()
                ->constrained('line_types')
                ->nullOnDelete();
            $table->string('slug', 70)->unique();
            $table->string('user_name');
            $table->string('number');
            $table->boolean('archived')->default(false);
            $table->string('mobistar')->nullable();
            $table->string('proximus')->nullable();
            $table->string('service')->nullable();
            $table->string('department')->nullable();
            $table->string('budget_article')->nullable();
            $table->string('location')->nullable();
            $table->string('fixed_cost')->nullable();
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-telecommunication')->dropIfExists('telephones');
    }
};
