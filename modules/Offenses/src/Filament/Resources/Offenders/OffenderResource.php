<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders;

use AcMarche\Offenses\Filament\Resources\Offenders\Pages\CreateOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\Pages\EditOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\Pages\ListOffenders;
use AcMarche\Offenses\Filament\Resources\Offenders\Pages\ViewOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\Schemas\OffenderForm;
use AcMarche\Offenses\Filament\Resources\Offenders\Tables\OffenderTables;
use AcMarche\Offenses\Models\Offender;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class OffenderResource extends Resource
{
    #[Override]
    protected static ?string $model = Offender::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationLabel(): string
    {
        return 'Contrevenants';
    }

    public static function form(Schema $schema): Schema
    {
        return OffenderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffenderTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffenders::route('/'),
            'create' => CreateOffender::route('/create'),
            'view' => ViewOffender::route('/{record}/view'),
            'edit' => EditOffender::route('/{record}/edit'),
        ];
    }
}
