<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Cours;

use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\CreateCours;
use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\EditCours;
use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\ListCours;
use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\ViewCours;
use AcMarche\ActivityManager\Filament\Resources\Cours\RelationManagers\DatesCoursRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Cours\RelationManagers\MembresRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Cours\Schemas\CoursForm;
use AcMarche\ActivityManager\Filament\Resources\Cours\Tables\CoursTable;
use AcMarche\ActivityManager\Models\Cours;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class CoursResource extends Resource
{
    #[Override]
    protected static ?string $model = Cours::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Activités';

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Cours';

    #[Override]
    protected static ?string $modelLabel = 'cours';

    #[Override]
    protected static ?string $pluralModelLabel = 'cours';

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
        return CoursForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoursTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DatesCoursRelationManager::class,
            MembresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCours::route('/'),
            'create' => CreateCours::route('/create'),
            'view' => ViewCours::route('/{record}'),
            'edit' => EditCours::route('/{record}/edit'),
        ];
    }
}
