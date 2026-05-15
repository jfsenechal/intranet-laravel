<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groupes;

use AcMarche\SportsActivities\Filament\Resources\Groupes\Pages\CreateGroupe;
use AcMarche\SportsActivities\Filament\Resources\Groupes\Pages\EditGroupe;
use AcMarche\SportsActivities\Filament\Resources\Groupes\Pages\ListGroupes;
use AcMarche\SportsActivities\Filament\Resources\Groupes\Pages\ViewGroupe;
use AcMarche\SportsActivities\Filament\Resources\Groupes\RelationManagers\InscriptionsRelationManager;
use AcMarche\SportsActivities\Filament\Resources\Groupes\Schemas\GroupeForm;
use AcMarche\SportsActivities\Filament\Resources\Groupes\Tables\GroupesTable;
use AcMarche\SportsActivities\Models\Groupe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class GroupeResource extends Resource
{
    #[Override]
    protected static ?string $model = Groupe::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Groupes';

    #[Override]
    protected static ?string $modelLabel = 'groupe';

    #[Override]
    protected static ?string $pluralModelLabel = 'groupes';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'jour',
            'lieux',
            'age',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return GroupeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroupes::route('/'),
            'create' => CreateGroupe::route('/create'),
            'view' => ViewGroupe::route('/{record}'),
            'edit' => EditGroupe::route('/{record}/edit'),
        ];
    }
}
