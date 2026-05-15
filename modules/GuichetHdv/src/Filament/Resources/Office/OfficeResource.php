<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Office;

use AcMarche\GuichetHdv\Filament\Resources\Office\Pages\CreateOffice;
use AcMarche\GuichetHdv\Filament\Resources\Office\Pages\EditOffice;
use AcMarche\GuichetHdv\Filament\Resources\Office\Pages\ListOffice;
use AcMarche\GuichetHdv\Filament\Resources\Office\Schemas\OfficeForm;
use AcMarche\GuichetHdv\Filament\Resources\Office\Tables\OfficeTables;
use AcMarche\GuichetHdv\Models\Office;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class OfficeResource extends Resource
{
    #[Override]
    protected static ?string $model = Office::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-office';
    }

    public static function getNavigationLabel(): string
    {
        return 'Guichets';
    }

    public static function form(Schema $schema): Schema
    {
        return OfficeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffice::route('/'),
            'create' => CreateOffice::route('/create'),
            'edit' => EditOffice::route('/{record}/edit'),
        ];
    }
}
