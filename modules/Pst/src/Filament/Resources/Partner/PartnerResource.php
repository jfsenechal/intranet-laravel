<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Partner;

use AcMarche\Pst\Enums\NavigationGroupEnum;
use AcMarche\Pst\Filament\Concerns\InteractsWithScoutGlobalSearch;
use AcMarche\Pst\Filament\Resources\Partner\Pages\CreatePartner;
use AcMarche\Pst\Filament\Resources\Partner\Pages\EditPartner;
use AcMarche\Pst\Filament\Resources\Partner\Pages\ListPartners;
use AcMarche\Pst\Filament\Resources\Partner\Pages\ViewPartner;
use AcMarche\Pst\Filament\Resources\Partner\Schemas\PartnerForm;
use AcMarche\Pst\Filament\Resources\Partner\Tables\PartnerTables;
use AcMarche\Pst\Models\Partner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class PartnerResource extends Resource
{
    use InteractsWithScoutGlobalSearch;

    #[Override]
    protected static ?string $model = Partner::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::Settings;

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'Partenaires externes';
    }

    public static function getModelLabel(): string
    {
        return 'Partenaire externe';
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerTables::configure($table);
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
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'view' => ViewPartner::route('/{record}'),
            'edit' => EditPartner::route('/{record}/edit'),
        ];
    }
}
