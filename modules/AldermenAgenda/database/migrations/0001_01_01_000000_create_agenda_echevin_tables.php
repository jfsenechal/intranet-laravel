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
        if (Schema::connection('maria-aldermen-agenda')->hasTable('agenda_echevin_recipients')) {
            return;
        }

        Schema::connection('maria-aldermen-agenda')->create('agenda_echevin_recipients', function (Blueprint $table): void {
            $table->id();
            $table->string('slugname', 70)->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email');
            $table->boolean('ics')->default(true);
            $table->string('token');
            $table->timestamps();
        });

        Schema::connection('maria-aldermen-agenda')->create('agenda_echevin_events', function (Blueprint $table): void {
            $table->id();
            $table->string('slugname', 70)->unique();
            $table->string('type_event');
            $table->string('intitule');
            $table->longText('object');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->dateTime('date_rappel')->nullable();
            $table->boolean('is_marche')->default(true);
            $table->string('organisateur');
            $table->string('lieu')->nullable();
            $table->string('representant')->nullable();
            $table->boolean('sent')->default(false);
            $table->string('file1_name', 255)->nullable();
            $table->string('file2_name', 255)->nullable();
            $table->timestamps();
        });

        Schema::connection('maria-aldermen-agenda')->create('agenda_echevin_participations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained('agenda_echevin_events')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('agenda_echevin_recipients')->cascadeOnDelete();
            $table->boolean('response')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'recipient_id']);
        });

        Schema::connection('maria-aldermen-agenda')->create('agenda_echevin_archives', function (Blueprint $table): void {
            $table->id();
            $table->string('intitule')->nullable();
            $table->longText('destinataires');
            $table->dateTime('date_envoie');
            $table->longText('contenu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-aldermen-agenda')->dropIfExists('agenda_echevin_participations');
        Schema::connection('maria-aldermen-agenda')->dropIfExists('agenda_echevin_events');
        Schema::connection('maria-aldermen-agenda')->dropIfExists('agenda_echevin_recipients');
        Schema::connection('maria-aldermen-agenda')->dropIfExists('agenda_echevin_archives');
    }
};
