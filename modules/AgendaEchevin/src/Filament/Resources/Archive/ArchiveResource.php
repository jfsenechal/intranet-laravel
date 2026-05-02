<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Archive;

use AcMarche\AgendaEchevin\Filament\Resources\Archive\Pages\CreateArchive;
use AcMarche\AgendaEchevin\Filament\Resources\Archive\Pages\EditArchive;
use AcMarche\AgendaEchevin\Filament\Resources\Archive\Pages\ListArchives;
use AcMarche\AgendaEchevin\Filament\Resources\Archive\Pages\ViewArchive;
use AcMarche\AgendaEchevin\Filament\Resources\Archive\Schemas\ArchiveForm;
use AcMarche\AgendaEchevin\Filament\Resources\Archive\Tables\ArchiveTables;
use AcMarche\AgendaEchevin\Models\Archive;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class ArchiveResource extends Resource
{
    #[Override]
    protected static ?string $model = Archive::class;

    #[Override]
    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-archive-box';
    }

    public static function getNavigationLabel(): string
    {
        return 'Archives';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Agenda Échevin';
    }

    public static function form(Schema $schema): Schema
    {
        return ArchiveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArchiveTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArchives::route('/'),
            'create' => CreateArchive::route('/create'),
            'edit' => EditArchive::route('/{record}/edit'),
            'view' => ViewArchive::route('/{record}'),
        ];
    }
}
