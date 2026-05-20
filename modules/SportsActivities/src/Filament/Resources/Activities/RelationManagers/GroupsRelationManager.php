<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
                TextInput::make('day')->label('Jour')->required()->maxLength(255),
                TextInput::make('time')->label('Heure')->required()->maxLength(255),
                TextInput::make('location')->label('Lieu')->required()->maxLength(255),
                TextInput::make('age')->label('Âge')->required()->maxLength(255),
                TextInput::make('price')->label('Prix')->numeric()->required()->default(0),
                TextInput::make('user')->label('Créé par')->required()->maxLength(255),
            ]),
            Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
            Textarea::make('comment')->label('Remarque')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day')
            ->columns([
                TextColumn::make('day')->label('Jour')->sortable()->searchable(),
                TextColumn::make('time')->label('Heure')->sortable(),
                TextColumn::make('location')->label('Lieu')->sortable()->searchable(),
                TextColumn::make('age')->label('Âge'),
                TextColumn::make('price')->label('Prix')->money('EUR')->sortable(),
                TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Inscriptions'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouveau groupe')
                    ->icon(Heroicon::Plus),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->icon(Heroicon::Trash),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
