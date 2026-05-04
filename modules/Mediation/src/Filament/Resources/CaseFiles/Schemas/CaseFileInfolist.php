<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CaseFileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Dossier')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('number')
                            ->label('Numéro')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('nature')
                            ->label('Nature'),
                        TextEntry::make('introduction_date')
                            ->label("Date d'introduction")
                            ->date('d/m/Y'),
                        TextEntry::make('closing_date')
                            ->label('Date de clôture')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        TextEntry::make('agreementType.name')
                            ->label("Type d'accord")
                            ->placeholder('—'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->prose()
                            ->columnSpanFull()
                            ->hidden(fn ($state): bool => blank($state)),
                    ]),

                Section::make('Plaignant')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('complainant.salutation')
                            ->label('Civilité')
                            ->placeholder('—'),
                        TextEntry::make('complainant.last_name')
                            ->label('Nom'),
                        TextEntry::make('complainant.first_name')
                            ->label('Prénom'),
                        TextEntry::make('complainant.birth_date')
                            ->label('Date de naissance')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        TextEntry::make('complainant.street')
                            ->label('Rue')
                            ->placeholder('—'),
                        TextEntry::make('complainant.postal_code')
                            ->label('Code postal')
                            ->placeholder('—'),
                        TextEntry::make('complainant.city')
                            ->label('Ville')
                            ->placeholder('—'),
                    ]),

                Section::make('Métadonnées')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('user_add')
                            ->label('Créé par')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Date de création')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Dernière modification')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
