<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Pvs;

use AcMarche\Conseil\Filament\Resources\Pvs\Pages\CreatePv;
use AcMarche\Conseil\Filament\Resources\Pvs\Pages\EditPv;
use AcMarche\Conseil\Filament\Resources\Pvs\Pages\ListPvs;
use AcMarche\Conseil\Filament\Resources\Pvs\Pages\ViewPv;
use AcMarche\Conseil\Filament\Resources\Pvs\Schemas\PvForm;
use AcMarche\Conseil\Filament\Resources\Pvs\Tables\PvsTable;
use AcMarche\Conseil\Models\Pv;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class PvResource extends Resource
{
    #[Override]
    protected static ?string $model = Pv::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    #[Override]
    protected static ?int $navigationSort = 4;

    #[Override]
    protected static ?string $navigationLabel = 'Procès-verbaux';

    #[Override]
    protected static ?string $modelLabel = 'procès-verbal';

    #[Override]
    protected static ?string $pluralModelLabel = 'procès-verbaux';

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
        return PvForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PvsTable::configure($table);
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
            'index' => ListPvs::route('/'),
            'create' => CreatePv::route('/create'),
            'view' => ViewPv::route('/{record}'),
            'edit' => EditPv::route('/{record}/edit'),
        ];
    }
}
