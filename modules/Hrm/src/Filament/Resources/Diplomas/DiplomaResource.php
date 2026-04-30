<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Diplomas;

use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\CreateDiploma;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\EditDiploma;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\ListDiplomas;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\ViewDiploma;
use AcMarche\Hrm\Filament\Resources\Diplomas\Schemas\DiplomaForm;
use AcMarche\Hrm\Filament\Resources\Diplomas\Schemas\DiplomaInfolist;
use AcMarche\Hrm\Filament\Resources\Diplomas\Tables\DiplomaTables;
use AcMarche\Hrm\Models\Diploma;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class DiplomaResource extends Resource
{
    #[Override]
    protected static ?string $model = Diploma::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-check';
    }

    public static function getNavigationLabel(): string
    {
        return 'Diplômes';
    }

    public static function getModelLabel(): string
    {
        return 'Diplôme';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Diplômes';
    }

    public static function form(Schema $schema): Schema
    {
        return DiplomaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DiplomaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiplomaTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiplomas::route('/'),
            'create' => CreateDiploma::route('/create'),
            'view' => ViewDiploma::route('/{record}/view'),
            'edit' => EditDiploma::route('/{record}/edit'),
        ];
    }
}
