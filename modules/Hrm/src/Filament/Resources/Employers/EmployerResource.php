<?php

namespace AcMarche\Hrm\Filament\Resources\Employers;

use AcMarche\Hrm\Models\Employer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

final class EmployerResource extends Resource
{
    protected static ?string $model = Employer::class;

    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-office';
    }

    public static function getNavigationLabel(): string
    {
        return 'Employeurs';
    }

    public static function getModelLabel(): string
    {
        return 'Employeur';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Employeurs';
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
                            ->maxLength(150)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\Select::make('parent_id')
                            ->label('Employeur parent')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('directions_count')
                    ->label('Directions')
                    ->counts('directions')
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
            'index' => Pages\ListEmployers::route('/'),
            'create' => Pages\CreateEmployer::route('/create'),
            'edit' => Pages\EditEmployer::route('/{record}/edit'),
        ];
    }
}
