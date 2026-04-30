<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

final class ListOptions
{
    public static function getNiveauxDiplomes(): array
    {
        $nidi_tmp = [
            "Certificat d'Enseignement Primaire",
            "Certificat d'Enseignement Secondaire Inférieur",
            "Certificat d'Enseignement Secondaire Supérieur",
            "Certificat d'Enseignement Technique Inférieur",
            "Certificat d'Enseignement Technique Supérieur",
            "Certificat d'Enseignement Professionnel Inférieur",
            "Certificat d'Enseignement Professionnel Supérieur",
            "Certificat d'Enseignement Qualification inférieur",
            "Certificat d'Enseignement Qualification supérieur",
            "Certificat d'Enseignement Supérieur de type court (Baccalauréat)",
            "Certificat d'Enseignement Supérieur de type long (Master)",
            'Néant',
        ];
        $nidi = [];
        foreach ($nidi_tmp as $valeur) {
            $nidi[$valeur] = $valeur;
        }

        return $nidi;
    }

    public static function getNiveauxDiplomesSimplifies(): array
    {
        $nidi_tmp = [
            "Certificat d'Enseignement Primaire",
            "Certificat d'Enseignement Secondaire",
            "Certificat d'Enseignement Baccalauréat",
            "Certificat d'Enseignement Master",
            'Néant',
        ];
        $nidi = [];
        foreach ($nidi_tmp as $valeur) {
            $nidi[$valeur] = $valeur;
        }

        return $nidi;
    }

    public static function getQuoteEvaluation(): array
    {
        $nidi_tmp = [
            'Excellent',
            'Très positive',
            'Néant',
            'Positive',
            'Satisfaisante',
            'A améliorer',
            'Insuffisante',
        ];
        $nidi = [];
        foreach ($nidi_tmp as $valeur) {
            $nidi[$valeur] = $valeur;
        }

        return $nidi;
    }

    public static function allowances(): array
    {
        return [
            'Aucune' => 'Aucune',
            'Indemnité de résidence' => 'Indemnité de résidence',
            'Indemnité de foyer' => 'Indemnité de foyer',
        ];
    }

    public static function getAccordePar(): array
    {
        $types_tmp = ['Bureau permanent', 'Collège', 'Conseil'];
        $types = [];
        foreach ($types_tmp as $type) {
            $types[$type] = $type;
        }

        return $types;
    }

    public static function getTypeFormation(): array
    {
        $types_tmp = ['Type1', 'Type2', 'Type3'];
        $types = [];
        foreach ($types_tmp as $type) {
            $types[$type] = mb_strtolower($type);
        }

        return $types;
    }

    public static function getRaisonsAbsence(): array
    {
        $raison_tmp = [
            'Maladie',
            'Accident',
            'Circonstance',
            'Circonstance maladie 1 jour',
            'Circonstance maladie 1/2 jour',
            'Congés exceptionnels',
            'Paternité',
            'Maladie Acc Conv',
            'Sans Certificat',
            'Dispense',
            'Maternité',
            'Rechute',
            'Sang',
            'Syndicat',
            'Maladie Retour',
        ];
        asort($raison_tmp);
        $raisons = [];

        foreach ($raison_tmp as $raison) {
            $raisons[$raison] = mb_strtolower($raison);
        }

        return $raisons;
    }
}
