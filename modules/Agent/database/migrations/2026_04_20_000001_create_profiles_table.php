<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-agent';

    public function up(): void
    {
        if (Schema::connection('maria-agent')->hasTable('agent')) {
            Schema::connection('maria-agent')->table('agent', function (Blueprint $table): void {
                $table->rename('profiles');
            });
            Schema::connection('maria-agent')->table('profiles', function (Blueprint $table): void {
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
                $table->renameColumn('emplacement', 'location');
                $table->renameColumn('remarques', 'notes');
                $table->renameColumn('responsables', 'supervisors');
                $table->renameColumn('employe_id', 'employee_id');
            });
            Schema::connection('maria-agent')->table('profiles', function (Blueprint $table): void {
                if (! Schema::connection('maria-agent')->hasColumn('profiles', 'created_at')) {
                    $table->timestamps();
                }
                // todo ->nullable(false)
                // $table->string('username')->nullable(false)->change();
            });
        } elseif (! Schema::connection('maria-agent')->hasTable('profiles')) {
            Schema::connection('maria-agent')->create('profiles', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('username')->unique();
                $table->json('emails');
                $table->json('supervisors')->nullable();
                $table->string('location')->nullable();
                $table->longText('notes')->nullable();
                $table->json('modules');
                $table->unsignedInteger('employee_id')->nullable();
                $table->uuid();
                $table->boolean('no_mail')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('maria-agent')->dropIfExists('profiles');
    }
};
