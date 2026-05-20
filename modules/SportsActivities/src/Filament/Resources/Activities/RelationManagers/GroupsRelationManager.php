<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\RelationManagers;

use AcMarche\SportsActivities\Filament\Resources\Groups\Tables\GroupsTable;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class GroupsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'groups';

    #[Override]
    protected static ?string $title = 'Groupes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('day')
                    ->label('Jour')
                    ->required()
                    ->maxLength(255),
                TextInput::make('time')
                    ->label('Heure')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('Lieu')
                    ->required()->maxLength(255),
                TextInput::make('age')
                    ->label('Âge')
                    ->required()->maxLength(255),
                TextInput::make('price')
                    ->label('Prix')
                    ->numeric()->required()
                    ->default(0),
                TextInput::make('user')
                    ->label('Créé par')
                    ->required()
                    ->maxLength(255),
            ]),
            Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
            Textarea::make('comment')->label('Remarque')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }
}
