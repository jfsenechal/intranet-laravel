<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        if (Schema::connection('maria-courrier')->hasTable('destinataire')) {
            Schema::connection('maria-courrier')->table('destinataire', function (Blueprint $table) {
                $table->rename('recipients');
            });
            Schema::connection('maria-courrier')->table('recipients', function (Blueprint $table) {
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('actif', 'is_active');
                $table->renameColumn('tuteur_id', 'supervisor_id');
                $table->renameColumn('slugname', 'slug');
                $table->renameColumn('attach', 'receives_attachments');
                $table->boolean('is_active')->default(true)->change();
                $table->boolean('receives_attachments')->default(false)->change();
            });

        } else {
            Schema::connection('maria-courrier')->create('recipients', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supervisor_id')->nullable()->constrained('recipients')->nullOnDelete();
                $table->string('slug', 70)->unique();
                $table->string('last_name');
                $table->string('first_name');
                $table->string('username');
                $table->string('email');
                $table->boolean('is_active')->default(true);
                $table->boolean('receives_attachments')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->dropIfExists('recipients');
    }
};
