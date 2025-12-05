<?php

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

final class PersonalInformationResource extends Resource
{
    protected static ?string $model = PersonalInformation::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'username';

    protected static ?string $modelLabel = 'Mes informations personnelles';
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
