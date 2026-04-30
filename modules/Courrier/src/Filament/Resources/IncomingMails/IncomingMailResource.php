<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails;

use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\EditIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\ListIncomingMails;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\ViewIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas\IncomingMailForm;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Tables\IncomingMailTables;
use AcMarche\Courrier\Models\IncomingMail;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class IncomingMailResource extends Resource
{
    #[Override]
    protected static ?string $model = IncomingMail::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-envelope';
    }

    public static function getNavigationLabel(): string
    {
        return 'Courrier entrant';
    }

    public static function getModelLabel(): string
    {
        return 'courrier';
    }

    public static function getPluralModelLabel(): string
    {
        return 'courriers';
    }

    public static function form(Schema $schema): Schema
    {
        return IncomingMailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomingMailTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomingMails::route('/'),
            'create' => CreateIncomingMail::route('/create'),
            'view' => ViewIncomingMail::route('/{record}/view'),
            'edit' => EditIncomingMail::route('/{record}/edit'),
        ];
    }
}
