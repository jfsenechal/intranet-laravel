<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\PersonalInformation;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\Pages\ManagePersonalInformation;
use AcMarche\Mileage\Filament\Resources\PersonalInformation\Schemas\PersonalInformationForm;
use AcMarche\Mileage\Filament\Resources\PersonalInformation\Schemas\PersonalInformationInfolist;
use AcMarche\Mileage\Filament\Resources\PersonalInformation\Tables\PersonalInformationTable;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class PersonalInformationResource extends Resource
{
    #[Override]
    protected static ?string $model = PersonalInformation::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    #[Override]
    protected static ?string $recordTitleAttribute = 'username';

    #[Override]
    protected static ?string $modelLabel = 'Mes informations personnelles';

    #[Override]
    protected static ?string $navigationLabel = 'Mes informations personnelles';

    public static function form(Schema $schema): Schema
    {
        return PersonalInformationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PersonalInformationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonalInformationTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return PersonalInformationRepository::modifyQueryToGetByCurrentUser(parent::getEloquentQuery());
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePersonalInformation::route('/'),
        ];
    }
}
