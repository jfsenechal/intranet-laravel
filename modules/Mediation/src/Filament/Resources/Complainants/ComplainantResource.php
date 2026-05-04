<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants;

use AcMarche\Mediation\Filament\Resources\Complainants\Pages\CreateComplainant;
use AcMarche\Mediation\Filament\Resources\Complainants\Pages\EditComplainant;
use AcMarche\Mediation\Filament\Resources\Complainants\Pages\ListComplainants;
use AcMarche\Mediation\Filament\Resources\Complainants\Pages\ViewComplainant;
use AcMarche\Mediation\Filament\Resources\Complainants\Schemas\ComplainantForm;
use AcMarche\Mediation\Filament\Resources\Complainants\Tables\ComplainantTables;
use AcMarche\Mediation\Models\Complainant;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class ComplainantResource extends Resource
{
    #[Override]
    protected static ?string $model = Complainant::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationLabel(): string
    {
        return 'Plaignants';
    }

    public static function form(Schema $schema): Schema
    {
        return ComplainantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplainantTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComplainants::route('/'),
            'create' => CreateComplainant::route('/create'),
            'view' => ViewComplainant::route('/{record}/view'),
            'edit' => EditComplainant::route('/{record}/edit'),
        ];
    }
}
