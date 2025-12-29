<?php

namespace AcMarche\Hrm\Filament\Resources\JobFunctions;

use AcMarche\Hrm\Models\JobFunction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class JobFunctionResource extends Resource
{
    protected static ?string $model = JobFunction::class;

    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-briefcase';
    }

    public static function getNavigationLabel(): string
    {
        return 'Fonctions';
    }

    public static function getModelLabel(): string
    {
        return 'Fonction';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Fonctions';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(150),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobFunctions::route('/'),
            'create' => Pages\CreateJobFunction::route('/create'),
            'edit' => Pages\EditJobFunction::route('/{record}/edit'),
        ];
    }
}
