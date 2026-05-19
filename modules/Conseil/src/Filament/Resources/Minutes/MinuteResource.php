<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Minutes;

use AcMarche\Conseil\Filament\Resources\Minutes\Pages\CreateMinute;
use AcMarche\Conseil\Filament\Resources\Minutes\Pages\EditMinute;
use AcMarche\Conseil\Filament\Resources\Minutes\Pages\ListMinutes;
use AcMarche\Conseil\Filament\Resources\Minutes\Pages\ViewMinute;
use AcMarche\Conseil\Filament\Resources\Minutes\Schemas\MinuteForm;
use AcMarche\Conseil\Filament\Resources\Minutes\Tables\MinutesTable;
use AcMarche\Conseil\Models\Minute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class MinuteResource extends Resource
{
    #[Override]
    protected static ?string $model = Minute::class;

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
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return MinuteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinutesTable::configure($table);
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
            'index' => ListMinutes::route('/'),
            'create' => CreateMinute::route('/create'),
            'view' => ViewMinute::route('/{record}'),
            'edit' => EditMinute::route('/{record}/edit'),
        ];
    }
}
