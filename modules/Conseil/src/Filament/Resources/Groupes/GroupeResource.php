<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groupes;

use AcMarche\Conseil\Filament\Resources\Groupes\Pages\CreateGroupe;
use AcMarche\Conseil\Filament\Resources\Groupes\Pages\EditGroupe;
use AcMarche\Conseil\Filament\Resources\Groupes\Pages\ListGroupes;
use AcMarche\Conseil\Filament\Resources\Groupes\Pages\ViewGroupe;
use AcMarche\Conseil\Filament\Resources\Groupes\RelationManagers\DestinatairesRelationManager;
use AcMarche\Conseil\Filament\Resources\Groupes\RelationManagers\PiecesJointesRelationManager;
use AcMarche\Conseil\Filament\Resources\Groupes\Schemas\GroupeForm;
use AcMarche\Conseil\Filament\Resources\Groupes\Tables\GroupesTable;
use AcMarche\Conseil\Models\Groupe;
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

    #[Override]
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nom',
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
            DestinatairesRelationManager::class,
            PiecesJointesRelationManager::class,
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
