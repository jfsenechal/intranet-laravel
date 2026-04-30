<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Valorizations;

use AcMarche\Hrm\Filament\Resources\Valorizations\Pages\CreateValorization;
use AcMarche\Hrm\Filament\Resources\Valorizations\Pages\EditValorization;
use AcMarche\Hrm\Filament\Resources\Valorizations\Pages\ListValorizations;
use AcMarche\Hrm\Filament\Resources\Valorizations\Pages\ViewValorization;
use AcMarche\Hrm\Filament\Resources\Valorizations\Schemas\ValorizationForm;
use AcMarche\Hrm\Filament\Resources\Valorizations\Schemas\ValorizationInfolist;
use AcMarche\Hrm\Filament\Resources\Valorizations\Tables\ValorizationTables;
use AcMarche\Hrm\Models\Valorization;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ValorizationResource extends Resource
{
    #[Override]
    protected static ?string $model = Valorization::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Personnel';

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static ?string $recordTitleAttribute = 'employer_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-currency-euro';
    }

    public static function getNavigationLabel(): string
    {
        return 'Valorisations';
    }

    public static function getModelLabel(): string
    {
        return 'Valorisation';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Valorisations';
    }

    public static function form(Schema $schema): Schema
    {
        return ValorizationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ValorizationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ValorizationTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListValorizations::route('/'),
            'create' => CreateValorization::route('/create'),
            'view' => ViewValorization::route('/{record}/view'),
            'edit' => EditValorization::route('/{record}/edit'),
        ];
    }
}
