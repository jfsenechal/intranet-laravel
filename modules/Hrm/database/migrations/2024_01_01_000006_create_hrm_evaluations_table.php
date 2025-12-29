<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('evaluation')) {
            Schema::connection($this->connection)->table('evaluation', function (Blueprint $table) {
                $table->rename('evaluations');
            });
            Schema::connection($this->connection)->table('evaluations', function (Blueprint $table) {
                $table->renameColumn('date_evaluation', 'evaluation_date');
                $table->renameColumn('date_prochaine', 'next_evaluation_date');
                $table->renameColumn('date_validation', 'validation_date');
                $table->renameColumn('resultat', 'result');
                $table->renameColumn('file1Name', 'file1_name');
                $table->renameColumn('file2Name', 'file2_name');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('evaluations')) {
            Schema::connection($this->connection)->create('evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id');
                $table->foreignId('direction_id')->nullable();
                $table->date('evaluation_date');
                $table->date('next_evaluation_date')->nullable();
                $table->date('validation_date')->nullable();
                $table->longText('notes')->nullable();
                $table->string('result', 200);
                $table->string('file1_name', 255)->nullable();
                $table->string('file2_name', 255)->nullable();
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('evaluations');
    }
};
