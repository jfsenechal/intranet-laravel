<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Archive;

use AcMarche\AldermenAgenda\Filament\Resources\Archive\Pages\ListArchives;
use AcMarche\AldermenAgenda\Filament\Resources\Archive\Pages\ViewArchive;
use AcMarche\AldermenAgenda\Filament\Resources\Archive\Tables\ArchiveTables;
use AcMarche\AldermenAgenda\Models\Archive;
use Filament\Resources\Resource;
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

    public static function table(Table $table): Table
    {
        return ArchiveTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArchives::route('/'),
            'view' => ViewArchive::route('/{record}'),
        ];
    }
}
