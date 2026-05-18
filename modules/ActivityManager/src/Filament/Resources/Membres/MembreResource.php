<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Membres;

use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\CreateMembre;
use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\EditMembre;
use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\ListMembres;
use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\ViewMembre;
use AcMarche\ActivityManager\Filament\Resources\Membres\RelationManagers\CoursRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Membres\Schemas\MembreForm;
use AcMarche\ActivityManager\Filament\Resources\Membres\Tables\MembresTable;
use AcMarche\ActivityManager\Models\Membre;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class MembreResource extends Resource
{
    #[Override]
    protected static ?string $model = Membre::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Activités';

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Membres';

    #[Override]
    protected static ?string $modelLabel = 'membre';

    #[Override]
    protected static ?string $pluralModelLabel = 'membres';

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
        return MembreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CoursRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembres::route('/'),
            'create' => CreateMembre::route('/create'),
            'view' => ViewMembre::route('/{record}'),
            'edit' => EditMembre::route('/{record}/edit'),
        ];
    }
}
