<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'maria-aldermen-agenda';

    public function up(): void
    {
        if (Schema::connection('maria-aldermen-agenda')->hasTable('destinataires')) {
            Schema::connection('maria-aldermen-agenda')->table('destinataires', function (Blueprint $table): void {
                $table->rename('aldermen_recipients');
            });
            Schema::connection('maria-aldermen-agenda')->table(
                'aldermen_recipients',
                function (Blueprint $table): void {
                    $table->renameColumn('nom', 'last_name');
                    $table->renameColumn('prenom', 'first_name');
                }
            );
        } else {
            Schema::connection('maria-aldermen-agenda')->create(
                'aldermen_recipients',
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
        }

        if (Schema::connection('maria-aldermen-agenda')->hasColumn('events', 'intitule')) {
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
                $table->renameColumn('created', 'created_at');
                $table->renameColumn('updated', 'updated_at');
            });
        } else {
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
        }

        if (Schema::connection('maria-aldermen-agenda')->hasTable('archives')) {
            Schema::connection('maria-aldermen-agenda')->table('archives', function (Blueprint $table): void {
                $table->rename('aldermen_archives');
            });
            Schema::connection('maria-aldermen-agenda')->table('aldermen_archives', function (Blueprint $table): void {
                $table->removeColumn('intitule');
                $table->renameColumn('contenu', 'content');
                $table->renameColumn('date_envoie', 'sent_at');
                $table->renameColumn('destinataires', 'recipients');
                $table->timestamps();
            });
        } else {
            Schema::connection('maria-aldermen-agenda')->create(
                'aldermen_archives',
                function (Blueprint $table): void {
                    $table->id();
                    $table->text('recipients');
                    $table->dateTime('sent_at');
                    $table->text('content');
                    $table->timestamps();
                }
            );
        }
    }
};
