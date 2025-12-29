<?php

namespace AcMarche\Hrm\Filament\Resources\HealthInsurances;

use AcMarche\Hrm\Models\HealthInsurance;
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

final class HealthInsuranceResource extends Resource
{
    protected static ?string $model = HealthInsurance::class;

    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 5;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-heart';
    }

    public static function getNavigationLabel(): string
    {
        return 'Mutuelles';
    }

    public static function getModelLabel(): string
    {
        return 'Mutuelle';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Mutuelles';
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
                            ->maxLength(100),
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
                Tables\Columns\TextColumn::make('employees_count')
                    ->label('Employés')
                    ->counts('employees')
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
            'index' => Pages\ListHealthInsurances::route('/'),
            'create' => Pages\CreateHealthInsurance::route('/create'),
            'edit' => Pages\EditHealthInsurance::route('/{record}/edit'),
        ];
    }
}
