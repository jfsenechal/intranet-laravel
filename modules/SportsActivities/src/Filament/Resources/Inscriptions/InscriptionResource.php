<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Inscriptions;

use AcMarche\SportsActivities\Filament\Resources\Inscriptions\Pages\CreateInscription;
use AcMarche\SportsActivities\Filament\Resources\Inscriptions\Pages\EditInscription;
use AcMarche\SportsActivities\Filament\Resources\Inscriptions\Pages\ListInscriptions;
use AcMarche\SportsActivities\Filament\Resources\Inscriptions\Pages\ViewInscription;
use AcMarche\SportsActivities\Filament\Resources\Inscriptions\Schemas\InscriptionForm;
use AcMarche\SportsActivities\Filament\Resources\Inscriptions\Tables\InscriptionsTable;
use AcMarche\SportsActivities\Models\Inscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class InscriptionResource extends Resource
{
    #[Override]
    protected static ?string $model = Inscription::class;

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
        return InscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InscriptionsTable::configure($table);
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
            'index' => ListInscriptions::route('/'),
            'create' => CreateInscription::route('/create'),
            'view' => ViewInscription::route('/{record}'),
            'edit' => EditInscription::route('/{record}/edit'),
        ];
    }
}
