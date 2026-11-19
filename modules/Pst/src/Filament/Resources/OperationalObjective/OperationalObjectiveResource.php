<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective;

use AcMarche\Pst\Filament\Concerns\InteractsWithScoutGlobalSearch;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\CreateOperationalObjective;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\EditOperationalObjective;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\ListOperationalObjectives;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\ViewOperationalObjective;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Schemas\OperationalObjectiveForm;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Tables\OperationalObjectiveTables;
use AcMarche\Pst\Models\OperationalObjective;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class OperationalObjectiveResource extends Resource
{
    use InteractsWithScoutGlobalSearch;

    #[Override]
    protected static ?string $model = OperationalObjective::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-target';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Objectif Opérationnel (OO)';
    }

    public static function table(Table $table): Table
    {
        return OperationalObjectiveTables::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return OperationalObjectiveForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOperationalObjectives::route('/'),
            'create' => CreateOperationalObjective::route('/create'),
            'view' => ViewOperationalObjective::route('/{record}'),
            'edit' => EditOperationalObjective::route('/{record}/edit'),
        ];
    }
}
