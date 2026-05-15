<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Database\Seeders;

use AcMarche\GuichetHdv\Models\Office;
use AcMarche\GuichetHdv\Models\Reason;
use Illuminate\Database\Seeder;

final class GuichetHdvSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedOffices();
        $this->seedReasons();
    }

    private function seedOffices(): void
    {
        $offices = [
            ['name' => 'Guichet 3A', 'service' => 'Pop'],
            ['name' => 'Guichet 3B', 'service' => 'Pop'],
            ['name' => 'Guichet 2', 'service' => 'Etranger'],
            ['name' => 'Guichet 1', 'service' => 'Etat-civil'],
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(['name' => $office['name']], $office);
        }
    }

    private function seedReasons(): void
    {
        $reasons = [
            'Absence temporaire',
            'Autorisation parentale',
            'Carte d\'identité (DEMANDE/RETRAIT)',
            'Carte de séjour (DEMANDE/RETRAIT)',
            'Certificat de vie',
            'Changement d\'adresse (DEMANDE)',
            'Cohabitation légale (DECLARATION)',
            'Cohabitation légale (CESSATION)',
            'Dernières volontés (DECLARATION)',
            'Don d\'organes',
            'Euthanasie (DECLARATION)',
            'Kids-id (DEMANDE / RETRAIT)',
            'Légalisation de signature et copie conforme',
            'Mise à jour de l\'adresse sur la carte d\'identité',
            'Nouveaux codes Pin/Puk',
            'Permis de conduire définitif (DEMANDE)',
            'Permis de conduire définitif (RETRAIT)',
            'Permis de conduire provisoire (DEMANDE)',
            'Permis de conduire provisoire (RETRAIT)',
            'Radiation pour l\'étranger',
            'Retrait document',
            'Perte/Vol de carte d\'identité (DECLARATION)',
            'Passeport (DEMANDE ou RETRAIT)',
        ];

        foreach ($reasons as $content) {
            Reason::firstOrCreate(['content' => $content]);
        }
    }
}
