<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles;

use AcMarche\Mediation\Filament\Resources\CaseFiles\Pages\CreateCaseFile;
use AcMarche\Mediation\Filament\Resources\CaseFiles\Pages\EditCaseFile;
use AcMarche\Mediation\Filament\Resources\CaseFiles\Pages\ListCaseFiles;
use AcMarche\Mediation\Filament\Resources\CaseFiles\Pages\ViewCaseFile;
use AcMarche\Mediation\Filament\Resources\CaseFiles\Schemas\CaseFileForm;
use AcMarche\Mediation\Filament\Resources\CaseFiles\Schemas\CaseFileInfolist;
use AcMarche\Mediation\Filament\Resources\CaseFiles\Tables\CaseFileTables;
use AcMarche\Mediation\Models\CaseFile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class CaseFileResource extends Resource
{
    #[Override]
    protected static ?string $model = CaseFile::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-folder';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dossiers';
    }

    public static function form(Schema $schema): Schema
    {
        return CaseFileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CaseFileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CaseFileTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCaseFiles::route('/'),
            'create' => CreateCaseFile::route('/create'),
            'view' => ViewCaseFile::route('/{record}/view'),
            'edit' => EditCaseFile::route('/{record}/edit'),
        ];
    }
}
