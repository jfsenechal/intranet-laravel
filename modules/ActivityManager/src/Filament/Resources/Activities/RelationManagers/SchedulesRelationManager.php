<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activities\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class SchedulesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'cours';

    #[Override]
    protected static ?string $title = 'Cours';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nom')
                ->label('Nom')
                ->required()
                ->maxLength(200)
                ->columnSpanFull(),
            Grid::make(2)->schema([
                DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->native(false),
                DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->afterOrEqual('date_debut'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                TextColumn::make('nom')->label('Nom')->searchable()->sortable()->limit(80)->wrap(),
                TextColumn::make('date_debut')->label('Début')->date('d/m/Y')->sortable(),
                TextColumn::make('date_fin')->label('Fin')->date('d/m/Y')->sortable()->placeholder('—'),
                TextColumn::make('membres_count')->counts('membres')->label('Inscrits'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouveau cours')
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
