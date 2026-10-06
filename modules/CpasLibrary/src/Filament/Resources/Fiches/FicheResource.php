<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\CreateFiche;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\EditFiche;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\ListFiches;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\ViewFiche;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Schemas\FicheForm;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Schemas\FicheInfolist;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Tables\FichesTable;
use AcMarche\CpasLibrary\Models\Fiche;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class FicheResource extends Resource
{
    #[Override]
    protected static ?string $model = Fiche::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Bibliothèque';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Fiches';

    #[Override]
    protected static ?string $modelLabel = 'fiche';

    #[Override]
    protected static ?string $pluralModelLabel = 'fiches';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
            'description',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return FicheForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FicheInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FichesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFiches::route('/'),
            'create' => CreateFiche::route('/create'),
            'view' => ViewFiche::route('/{record}'),
            'edit' => EditFiche::route('/{record}/edit'),
        ];
    }

    /**
     * "Nouvelle fiche" dropdown with one create link per fiche type, shared by
     * the fiche list and the library index.
     */
    public static function createActionGroup(): ActionGroup
    {
        return ActionGroup::make(
            array_map(
                fn (FicheTypeEnum $type): Action => Action::make('create_'.$type->value)
                    ->label($type->getLabel())
                    ->icon(Heroicon::Plus)
                    ->url(self::getUrl('create', ['type' => $type->value])),
                FicheTypeEnum::cases(),
            ),
        )
            ->label('Nouvelle fiche')
            ->icon(Heroicon::Plus)
            ->button()
            ->visible(fn (): bool => self::canCreate());
    }
}
