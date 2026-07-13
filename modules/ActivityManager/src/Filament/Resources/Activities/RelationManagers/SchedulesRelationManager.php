<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activities\RelationManagers;

use AcMarche\ActivityManager\Filament\Resources\Schedules\SchedulesResource;
use AcMarche\ActivityManager\Models\Schedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
    protected static string $relationship = 'schedules';

    #[Override]
    protected static ?string $title = 'Cours';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nom')
                ->required()
                ->maxLength(200)
                ->columnSpanFull(),
            Grid::make(2)->schema([
                DatePicker::make('start_date')
                    ->label('Date de début')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->native(false),
                DatePicker::make('end_date')
                    ->label('Date de fin')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->afterOrEqual('start_date'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('start_date', 'DESC')
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->sortable()->limit(80)->wrap(),
                TextColumn::make('start_date')->label('Début')->date('d/m/Y')->sortable(),
                TextColumn::make('end_date')->label('Fin')->date('d/m/Y')->sortable()->placeholder('—'),
                TextColumn::make('members_count')->counts('members')->label('Inscrits'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouveau cours')
                    ->icon(Heroicon::Plus),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir')
                    ->url(fn(Schedule $record): string => SchedulesResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->icon(Heroicon::Trash),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
