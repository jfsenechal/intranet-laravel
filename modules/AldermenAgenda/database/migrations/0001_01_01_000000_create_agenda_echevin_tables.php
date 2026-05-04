<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-aldermen-agenda';

    public function up(): void
    {
        if (Schema::connection('maria-aldermen-agenda')->hasTable('destinataires')) {
            Schema::connection('maria-aldermen-agenda')->table('destinataires', function (Blueprint $table): void {
                $table->rename('recipients');
            });
            Schema::connection('maria-aldermen-agenda')->table('recipients', function (Blueprint $table): void {
                $table->renameColumn('nom', 'last_name');
                $table->renameColumn('prenom', 'first_name');
            });
        }
        if (Schema::connection('maria-aldermen-agenda')->hasTable('events')) {
            Schema::connection('maria-aldermen-agenda')->table('events', function (Blueprint $table): void {
                $table->renameColumn('intitule', 'name');
                $table->renameColumn('type_event', 'event_type');
                $table->renameColumn('date_debut', 'start_at');
                $table->renameColumn('date_fin', 'end_at');
                $table->renameColumn('date_rappel', 'reminder_at');
                $table->renameColumn('is_marche', 'is_local');
                $table->renameColumn('organisateur', 'organizer');
                $table->renameColumn('lieu', 'location');
                $table->renameColumn('representant', 'representative');
                $table->renameColumn('date_rappel', 'file1_name');
                $table->renameColumn('date_rappel', 'file_name');
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated_at', 'file_name');
            });
        }
        if (Schema::connection('maria-aldermen-agenda')->hasTable('archives')) {
            Schema::connection('maria-aldermen-agenda')->table('archives', function (Blueprint $table): void {
                $table->rename('histories');
            });
            Schema::connection('maria-aldermen-agenda')->table('histories', function (Blueprint $table): void {
                $table->removeColumn('intitule');
                $table->renameColumn('contenu', 'content');
                $table->renameColumn('date_envoie', 'sent_at');
                $table->renameColumn('destinataires', 'recipients');
                $table->timestamps();
            });
        }

        if (Schema::connection('maria-aldermen-agenda')->hasTable('recipients')) {
            return;
        }
        Schema::connection('maria-aldermen-agenda')->create(
            'recipients',
            function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 70)->unique();
                $table->string('last_name');
                $table->string('first_name');
                $table->string('email');
                $table->boolean('ics')->default(true);
                $table->string('token');
            }
        );

        Schema::connection('maria-aldermen-agenda')->create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 70)->unique();
            $table->string('event_type');
            $table->string('title');
            $table->text('description');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('reminder_at')->nullable();
            $table->boolean('is_local')->default(true);
            $table->string('organizer');
            $table->string('location')->nullable();
            $table->string('representative')->nullable();
            $table->boolean('sent')->default(false);
            $table->string('file1_name', 255)->nullable();
            $table->string('file2_name', 255)->nullable();
            $table->timestamps();
        });

        Schema::connection('maria-aldermen-agenda')->create(
            'histories',
            function (Blueprint $table): void {
                $table->id();
                $table->text('recipients');
                $table->dateTime('sent_at');
                $table->text('content');
                $table->timestamps();
            }
        );
    }
};
