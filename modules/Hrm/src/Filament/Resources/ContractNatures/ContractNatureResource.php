<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\ContractNatures;

use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\CreateContractNature;
use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\EditContractNature;
use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\ListContractNatures;
use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\ViewContractNature;
use AcMarche\Hrm\Models\ContractNature;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ContractNatureResource extends Resource
{
    #[Override]
    protected static ?string $model = ContractNature::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 6;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Natures de contrat';
    }

    public static function getModelLabel(): string
    {
        return 'Nature de contrat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Natures de contrat';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(50)
                            ->live(onBlur: true),
                        TextInput::make('description')
                            ->label('Description')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                        TextEntry::make('employer.name')
                            ->label('Employeur'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
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
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->sortable()
                    ->toggleable(),
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
            'index' => ListContractNatures::route('/'),
            'create' => CreateContractNature::route('/create'),
            'view' => ViewContractNature::route('/{record}/view'),
            'edit' => EditContractNature::route('/{record}/edit'),
        ];
    }
}
