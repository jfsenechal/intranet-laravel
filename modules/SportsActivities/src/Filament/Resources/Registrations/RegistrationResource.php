<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations;

use AcMarche\SportsActivities\Filament\Resources\Registrations\Pages\CreateRegistration;
use AcMarche\SportsActivities\Filament\Resources\Registrations\Pages\EditRegistration;
use AcMarche\SportsActivities\Filament\Resources\Registrations\Pages\ListRegistrations;
use AcMarche\SportsActivities\Filament\Resources\Registrations\Pages\ViewRegistration;
use AcMarche\SportsActivities\Filament\Resources\Registrations\Schemas\RegistrationForm;
use AcMarche\SportsActivities\Filament\Resources\Registrations\Tables\RegistrationsTable;
use AcMarche\SportsActivities\Models\Registration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class RegistrationResource extends Resource
{
    #[Override]
    protected static ?string $model = Registration::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    #[Override]
    protected static ?int $navigationSort = 4;

    #[Override]
    protected static ?string $navigationLabel = 'Inscriptions';

    #[Override]
    protected static ?string $modelLabel = 'inscription';

    #[Override]
    protected static ?string $pluralModelLabel = 'inscriptions';

    public static function form(Schema $schema): Schema
    {
        return RegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistrationsTable::configure($table);
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
            'index' => ListRegistrations::route('/'),
            'create' => CreateRegistration::route('/create'),
            'view' => ViewRegistration::route('/{record}'),
            'edit' => EditRegistration::route('/{record}/edit'),
        ];
    }
}
