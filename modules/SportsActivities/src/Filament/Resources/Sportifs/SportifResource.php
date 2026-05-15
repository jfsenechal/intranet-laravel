<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Sportifs;

use AcMarche\SportsActivities\Filament\Resources\Sportifs\Pages\CreateSportif;
use AcMarche\SportsActivities\Filament\Resources\Sportifs\Pages\EditSportif;
use AcMarche\SportsActivities\Filament\Resources\Sportifs\Pages\ListSportifs;
use AcMarche\SportsActivities\Filament\Resources\Sportifs\Pages\ViewSportif;
use AcMarche\SportsActivities\Filament\Resources\Sportifs\Schemas\SportifForm;
use AcMarche\SportsActivities\Filament\Resources\Sportifs\Tables\SportifsTable;
use AcMarche\SportsActivities\Models\Sportif;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class SportifResource extends Resource
{
    #[Override]
    protected static ?string $model = Sportif::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Sportifs';

    #[Override]
    protected static ?string $modelLabel = 'sportif';

    #[Override]
    protected static ?string $pluralModelLabel = 'sportifs';

    #[Override]
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nom',
            'prenom',
            'email',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return SportifForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SportifsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSportifs::route('/'),
            'create' => CreateSportif::route('/create'),
            'view' => ViewSportif::route('/{record}'),
            'edit' => EditSportif::route('/{record}/edit'),
        ];
    }
}
