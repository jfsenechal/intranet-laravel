<?php

namespace AcMarche\App\Repository;

use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;

class FilamentColorRepository
{
    public static function userColor(): array
    {
        $colors = [
            'primary' => Color::Slate,
            'secondary' => Color::Pink,
        ];
        if ($user = Auth::user()) {
            if ($colorSelected = $user->color_primary) {
                $color = self::findConstantByValue($colorSelected);
                if ($color) {
                    $colors['primary'] = $color;
                }
            }
            if ($colorSelected = $user->color_secondary) {
                $color = self::findConstantByValue($colorSelected);
                if ($color) {
                    $colors['secondary'] = $color;
                }
            }
        }

        return $colors;
    }

    public static function colors(): array
    {
        return [
            '' => 'Défaut',
            'slate' => 'Ardoise',
            'gray' => 'Gris',
            'zinc' => 'Zinc',
            'neutral' => 'Neutre',
            'stone' => 'Pierre',
            'red' => 'Rouge',
            'orange' => 'Orange',
            'amber' => 'Ambre',
            'yellow' => 'Jaune',
            'lime' => 'Citron vert',
            'green' => 'Vert',
            'emerald' => 'Emeraude',
            'teal' => 'Sarcelle',
            'cyan' => 'Cyan',
            'sky' => 'Ciel',
            'blue' => 'Bleu',
            'indigo' => 'Indigo',
            'violet' => 'Violet',
            'purple' => 'Pourpre',
            'fuchsia' => 'Fuchsia',
            'pink' => 'Rose',
            'rose' => 'Rose',
        ];
    }

    public static function findConstantByValue(string $value): ?array
    {
        $reflection = new \ReflectionClass(Color::class);
        foreach ($reflection->getConstants() as $name => $colors) {
            if ($name === ucfirst($value)) {
                return $colors;
            }
        }

        return null;
    }
}
