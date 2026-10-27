<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\EditCitoyen;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\ListCitoyens;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\ViewCitoyen;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas\CitoyenForm;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas\CitoyenInfolist;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Tables\CitoyensTable;
use AcMarche\EmailManagement\Models\Citoyen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class CitoyenResource extends Resource
{
    protected static ?string $model = Citoyen::class;

    #[Override]
    protected static ?string $navigationLabel = 'Citoyens';

    #[Override]
    protected static ?string $modelLabel = 'citoyen';

    #[Override]
    protected static ?string $pluralModelLabel = 'Citoyens';

    #[Override]
    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'mail';

    public static function form(Schema $schema): Schema
    {
        return CitoyenForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CitoyenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CitoyensTable::configure($table);
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
            'index' => ListCitoyens::route('/'),
            'view' => ViewCitoyen::route('/{record}'),
            'edit' => EditCitoyen::route('/{record}/edit'),
        ];
    }
}
