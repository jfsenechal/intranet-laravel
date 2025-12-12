<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        // Skip if new tables already exist (fresh install)
        if (Schema::connection('maria-courrier')->hasTable('incoming_mails')
            && Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'reference_number')) {
            return;
        }

        // Check if legacy tables exist
        $hasLegacyCourrier = Schema::connection('maria-courrier')->hasTable('courrier');
        $hasLegacyService = Schema::connection('maria-courrier')->hasTable('service');
        $hasLegacyDestinataire = Schema::connection('maria-courrier')->hasTable('destinataire');

        if (! $hasLegacyCourrier) {
            return;
        }

        // Migrate courrier -> incoming_mails
        if ($hasLegacyCourrier && ! Schema::connection('maria-courrier')->hasTable('incoming_mails')) {
            Schema::connection('maria-courrier')->rename('courrier', 'incoming_mails');
        }

        // Rename columns in incoming_mails
        Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'numero')) {
                $table->renameColumn('numero', 'reference_number');
            }
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'expediteur')) {
                $table->renameColumn('expediteur', 'sender');
            }
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'date_courrier')) {
                $table->renameColumn('date_courrier', 'mail_date');
            }
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'notifie')) {
                $table->renameColumn('notifie', 'is_notified');
            }
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'recommande')) {
                $table->renameColumn('recommande', 'is_registered');
            }
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'accuse')) {
                $table->renameColumn('accuse', 'has_acknowledgment');
            }
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'created')) {
                $table->renameColumn('created', 'created_at');
            }
            if (Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'updated')) {
                $table->renameColumn('updated', 'updated_at');
            }
        });

        // Add soft deletes if not exists
        if (! Schema::connection('maria-courrier')->hasColumn('incoming_mails', 'deleted_at')) {
            Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }

        // Migrate service -> services
        if ($hasLegacyService && ! Schema::connection('maria-courrier')->hasTable('services')) {
            Schema::connection('maria-courrier')->rename('service', 'services');

            Schema::connection('maria-courrier')->table('services', function (Blueprint $table): void {
                if (Schema::connection('maria-courrier')->hasColumn('services', 'slugname')) {
                    $table->renameColumn('slugname', 'slug');
                }
                if (Schema::connection('maria-courrier')->hasColumn('services', 'nom')) {
                    $table->renameColumn('nom', 'name');
                }
                if (Schema::connection('maria-courrier')->hasColumn('services', 'actif')) {
                    $table->renameColumn('actif', 'is_active');
                }
            });

            // Add timestamps if not exists
            if (! Schema::connection('maria-courrier')->hasColumn('services', 'created_at')) {
                Schema::connection('maria-courrier')->table('services', function (Blueprint $table): void {
                    $table->timestamps();
                });
            }
        }

        // Migrate destinataire -> recipients
        if ($hasLegacyDestinataire && ! Schema::connection('maria-courrier')->hasTable('recipients')) {
            Schema::connection('maria-courrier')->rename('destinataire', 'recipients');

            Schema::connection('maria-courrier')->table('recipients', function (Blueprint $table): void {
                if (Schema::connection('maria-courrier')->hasColumn('recipients', 'tuteur_id')) {
                    $table->renameColumn('tuteur_id', 'supervisor_id');
                }
                if (Schema::connection('maria-courrier')->hasColumn('recipients', 'slugname')) {
                    $table->renameColumn('slugname', 'slug');
                }
                if (Schema::connection('maria-courrier')->hasColumn('recipients', 'nom')) {
                    $table->renameColumn('nom', 'last_name');
                }
                if (Schema::connection('maria-courrier')->hasColumn('recipients', 'prenom')) {
                    $table->renameColumn('prenom', 'first_name');
                }
                if (Schema::connection('maria-courrier')->hasColumn('recipients', 'actif')) {
                    $table->renameColumn('actif', 'is_active');
                }
                if (Schema::connection('maria-courrier')->hasColumn('recipients', 'attach')) {
                    $table->renameColumn('attach', 'receives_attachments');
                }
            });

            // Add timestamps if not exists
            if (! Schema::connection('maria-courrier')->hasColumn('recipients', 'created_at')) {
                Schema::connection('maria-courrier')->table('recipients', function (Blueprint $table): void {
                    $table->timestamps();
                });
            }
        }

        // Migrate courrier_service -> incoming_mail_service
        if (Schema::connection('maria-courrier')->hasTable('courrier_service')
            && ! Schema::connection('maria-courrier')->hasTable('incoming_mail_service')) {
            Schema::connection('maria-courrier')->rename('courrier_service', 'incoming_mail_service');

            Schema::connection('maria-courrier')->table('incoming_mail_service', function (Blueprint $table): void {
                if (Schema::connection('maria-courrier')->hasColumn('incoming_mail_service', 'courrier_id')) {
                    $table->renameColumn('courrier_id', 'incoming_mail_id');
                }
                if (Schema::connection('maria-courrier')->hasColumn('incoming_mail_service', 'principal')) {
                    $table->renameColumn('principal', 'is_primary');
                }
            });
        }

        // Migrate courrier_destinataire -> incoming_mail_recipient
        if (Schema::connection('maria-courrier')->hasTable('courrier_destinataire')
            && ! Schema::connection('maria-courrier')->hasTable('incoming_mail_recipient')) {
            Schema::connection('maria-courrier')->rename('courrier_destinataire', 'incoming_mail_recipient');

            Schema::connection('maria-courrier')->table('incoming_mail_recipient', function (Blueprint $table): void {
                if (Schema::connection('maria-courrier')->hasColumn('incoming_mail_recipient', 'courrier_id')) {
                    $table->renameColumn('courrier_id', 'incoming_mail_id');
                }
                if (Schema::connection('maria-courrier')->hasColumn('incoming_mail_recipient', 'destinataire_id')) {
                    $table->renameColumn('destinataire_id', 'recipient_id');
                }
                if (Schema::connection('maria-courrier')->hasColumn('incoming_mail_recipient', 'principal')) {
                    $table->renameColumn('principal', 'is_primary');
                }
            });
        }
    }

    public function down(): void
    {
        // This migration is not reversible as it would require restoring the old French names
        // which could cause data issues with any new code that relies on the English names
    }
};
