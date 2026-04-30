<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\HealthInsurances;

use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\CreateHealthInsurance;
use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\EditHealthInsurance;
use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\ListHealthInsurances;
use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\ViewHealthInsurance;
use AcMarche\Hrm\Models\HealthInsurance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class HealthInsuranceResource extends Resource
{
    #[Override]
    protected static ?string $model = HealthInsurance::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 5;

    public static function getNavigationIcon(): string
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
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employees_count')
                    ->label('Employés')
                    ->counts('employees')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHealthInsurances::route('/'),
            'create' => CreateHealthInsurance::route('/create'),
            'view' => ViewHealthInsurance::route('/{record}/view'),
            'edit' => EditHealthInsurance::route('/{record}/edit'),
        ];
    }
}
