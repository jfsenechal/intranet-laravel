<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\ContractTypes;

use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\CreateContractType;
use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\EditContractType;
use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\ListContractTypes;
use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\ViewContractType;
use AcMarche\Hrm\Models\ContractType;
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

final class ContractTypeResource extends Resource
{
    #[Override]
    protected static ?string $model = ContractType::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 9;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-check';
    }

    public static function getNavigationLabel(): string
    {
        return 'Types de contrat';
    }

    public static function getModelLabel(): string
    {
        return 'Type de contrat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Types de contrat';
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
            'index' => ListContractTypes::route('/'),
            'create' => CreateContractType::route('/create'),
            'view' => ViewContractType::route('/{record}/view'),
            'edit' => EditContractType::route('/{record}/edit'),
        ];
    }
}
