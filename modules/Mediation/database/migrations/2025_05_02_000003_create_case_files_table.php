<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-mediation')->hasTable('dossier')) {
            Schema::connection('maria-mediation')->table('dossier', function (Blueprint $table): void {
                $table->rename('case_files');
            });
            Schema::connection('maria-mediation')->table('case_files', function (Blueprint $table): void {
                $table->renameColumn('numero', 'number');
                $table->renameColumn('date_introduction', 'introduction_date');
                $table->renameColumn('date_cloture', 'closing_date');
                $table->renameColumn('plaignant_id', 'complainant_id');
                $table->renameColumn('accord_id', 'agreement_type_id');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } elseif (! Schema::connection('maria-mediation')->hasTable('case_files')) {
            Schema::connection('maria-mediation')->create('case_files', function (Blueprint $table): void {
                $table->id();
                $table->integer('number')->nullable();
                $table->date('introduction_date');
                $table->date('closing_date')->nullable();
                $table->string('nature');
                $table->text('description')->nullable();
                $table->foreignId('complainant_id')->constrained('complainants')->cascadeOnDelete();
                $table->foreignId('agreement_type_id')->nullable()->constrained('agreement_types')->nullOnDelete();
                $table->string('user_add');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-mediation')->dropIfExists('case_files');
    }
};
