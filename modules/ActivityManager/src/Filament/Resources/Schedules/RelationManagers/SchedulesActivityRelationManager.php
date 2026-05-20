<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class SchedulesActivityRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'datesSchedules';

    #[Override]
    protected static ?string $title = 'Séances';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DateTimePicker::make('jour')
                ->label('Date et heure')
                ->required()
                ->displayFormat('d/m/Y H:i')
                ->seconds(false)
                ->native(false),
            Textarea::make('remarque')
                ->label('Remarque')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('jour')
            ->recordTitleAttribute('jour')
            ->columns([
                TextColumn::make('jour')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('remarque')
                    ->label('Remarque')
                    ->limit(80)
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouvelle séance')
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
