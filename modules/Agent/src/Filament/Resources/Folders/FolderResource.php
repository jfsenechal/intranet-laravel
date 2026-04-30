<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Folders;

use AcMarche\Agent\Filament\Resources\Folders\Pages\CreateFolder;
use AcMarche\Agent\Filament\Resources\Folders\Pages\EditFolder;
use AcMarche\Agent\Filament\Resources\Folders\Pages\ListFolders;
use AcMarche\Agent\Filament\Resources\Folders\Schemas\FolderForm;
use AcMarche\Agent\Filament\Resources\Folders\Tables\FolderTables;
use AcMarche\Agent\Models\Folder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class FolderResource extends Resource
{
    #[Override]
    protected static ?string $model = Folder::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-folder';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dossiers partagés';
    }

    public static function getModelLabel(): string
    {
        return 'dossier partagé';
    }

    public static function getPluralModelLabel(): string
    {
        return 'dossiers partagés';
    }

    public static function form(Schema $schema): Schema
    {
        return FolderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FolderTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFolders::route('/'),
            'create' => CreateFolder::route('/create'),
            'edit' => EditFolder::route('/{record}/edit'),
        ];
    }
}
