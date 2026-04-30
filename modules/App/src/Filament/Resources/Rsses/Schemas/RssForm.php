<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Rsses\Schemas;

use AcMarche\App\Enums\RssFeedEnum;
use AcMarche\App\Rules\ValidRssFeed;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class RssForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Flux RSS')
                    ->description('Ajoutez un flux RSS personnalisé ou choisissez-en un prédéfini.')
                    ->columns(1)
                    ->schema([
                        Select::make('predefined')
                            ->label('Flux prédéfinis')
                            ->helperText('Sélectionnez un flux prédéfini pour pré-remplir le formulaire.')
                            ->options(RssFeedEnum::options())
                            ->searchable()
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if ($state === null || $state === '') {
                                    return;
                                }

                                $enum = RssFeedEnum::tryFrom($state);

                                if ($enum === null) {
                                    return;
                                }

                                $set('name', $enum->getLabel());
                                $set('url', $enum->value);
                            }),
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->required()
                            ->maxLength(255)
                            ->rules([new ValidRssFeed]),
                    ]),
            ]);
    }
}
