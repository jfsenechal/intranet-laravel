<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('diplome')) {
            Schema::connection($this->connection)->table('diplome', function (Blueprint $table): void {
                $table->rename('diplomas');
            });
            Schema::connection($this->connection)->table('diplomas', function (Blueprint $table): void {
                $table->renameColumn('employe_id', 'employee_id');
                $table->renameColumn('intitule', 'name');
                $table->renameColumn('attestation_name', 'certificate_file');
                $table->renameColumn('user', 'user_add');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
                $table->renameColumn('updateBy', 'updated_by');
            });
        } elseif (! Schema::connection($this->connection)->hasTable('diplomas')) {
            Schema::connection($this->connection)->create('diplomas', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('employee_id');
                $table->string('name', 150);
                $table->string('certificate_file', 255)->nullable();
                $table->string('user_add', 255);
                $table->string('updated_by', 255)->nullable();
                $table->timestamps();
            });
        }
    }
};
