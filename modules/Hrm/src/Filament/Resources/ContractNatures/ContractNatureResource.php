<?php

namespace AcMarche\Hrm\Filament\Resources\ContractNatures;

use AcMarche\Hrm\Models\ContractNature;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

final class ContractNatureResource extends Resource
{
    protected static ?string $model = ContractNature::class;

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): ?string
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
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(50)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(75),
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
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
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->sortable()
                    ->toggleable(),
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
            'index' => Pages\ListContractNatures::route('/'),
            'create' => Pages\CreateContractNature::route('/create'),
            'edit' => Pages\EditContractNature::route('/{record}/edit'),
        ];
    }
}
