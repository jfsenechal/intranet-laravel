<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\PayScales;

use AcMarche\Hrm\Filament\Resources\PayScales\Pages\CreatePayScale;
use AcMarche\Hrm\Filament\Resources\PayScales\Pages\EditPayScale;
use AcMarche\Hrm\Filament\Resources\PayScales\Pages\ListPayScales;
use AcMarche\Hrm\Filament\Resources\PayScales\Pages\ViewPayScale;
use AcMarche\Hrm\Models\PayScale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class PayScaleResource extends Resource
{
    #[Override]
    protected static ?string $model = PayScale::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-scale';
    }

    public static function getNavigationLabel(): string
    {
        return 'Echelles barémiques';
    }

    public static function getModelLabel(): string
    {
        return 'Echelle barémique';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Echelles barémiques';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Titre')
                            ->required()
                            ->maxLength(150),
                        Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
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
                            ->label('Titre'),
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
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->sortable()
                    ->toggleable(),
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
            'index' => ListPayScales::route('/'),
            'create' => CreatePayScale::route('/create'),
            'view' => ViewPayScale::route('/{record}/view'),
            'edit' => EditPayScale::route('/{record}/edit'),
        ];
    }
}
