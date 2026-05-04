<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\AgreementTypes;

use AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages\CreateAgreementType;
use AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages\EditAgreementType;
use AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages\ListAgreementTypes;
use AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages\ViewAgreementType;
use AcMarche\Mediation\Filament\Resources\AgreementTypes\Schemas\AgreementTypeForm;
use AcMarche\Mediation\Filament\Resources\AgreementTypes\Tables\AgreementTypeTables;
use AcMarche\Mediation\Models\AgreementType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class AgreementTypeResource extends Resource
{
    #[Override]
    protected static ?string $model = AgreementType::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationLabel(): string
    {
        return "Types d'accord";
    }

    public static function form(Schema $schema): Schema
    {
        return AgreementTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgreementTypeTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgreementTypes::route('/'),
            'create' => CreateAgreementType::route('/create'),
            'view' => ViewAgreementType::route('/{record}/view'),
            'edit' => EditAgreementType::route('/{record}/edit'),
        ];
    }
}
