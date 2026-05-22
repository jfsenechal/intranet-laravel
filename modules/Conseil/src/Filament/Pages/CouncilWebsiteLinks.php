<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;

final class CouncilWebsiteLinks extends Page
{
    #[Override]
    protected string $view = 'conseil::filament.pages.council-website-links';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    #[Override]
    protected static ?int $navigationSort = 5;

    #[Override]
    protected static ?string $navigationLabel = 'Le Conseil sur marche.be';

    public function getTitle(): string
    {
        return 'Le Conseil sur le site www.marche.be';
    }

    /**
     * @return array<int, array{title: string, url: string, label: string}>
     */
    public function getLinks(): array
    {
        return [
            [
                'title' => 'Ordre',
                'url' => 'https://www.marche.be/administration/le-conseil-communal/ordre-du-jour-du-conseil-1650/',
                'label' => "Consulter l'ordre du Conseil",
            ],
            [
                'title' => 'Les Pv',
                'url' => 'https://www.marche.be/administration/le-conseil-communal/les-pv-du-conseil-2-1651',
                'label' => 'Consulter la liste des PV',
            ],
            [
                'title' => 'Les membres',
                'url' => 'https://www.marche.be/administration/le-conseil-communal/composition-du-conseil-communal-1583/',
                'label' => 'Consulter les membres du Conseil',
            ],
        ];
    }
}
