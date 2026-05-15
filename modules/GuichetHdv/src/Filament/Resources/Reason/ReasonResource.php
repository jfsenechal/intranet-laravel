<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Reason;

use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\CreateReason;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\EditReason;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\ListReason;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Schemas\ReasonForm;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Tables\ReasonTables;
use AcMarche\GuichetHdv\Models\Reason;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class ReasonResource extends Resource
{
    #[Override]
    protected static ?string $model = Reason::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-list-bullet';
    }

    public static function getNavigationLabel(): string
    {
        return 'Motifs';
    }

    public static function form(Schema $schema): Schema
    {
        return ReasonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReasonTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReason::route('/'),
            'create' => CreateReason::route('/create'),
            'edit' => EditReason::route('/{record}/edit'),
        ];
    }
}
