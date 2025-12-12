<?php

namespace AcMarche\Courrier\Filament\Resources;

use AcMarche\Courrier\Filament\Resources\IncomingMailResource\Pages;
use AcMarche\Courrier\Filament\Resources\IncomingMailResource\Schema\IncomingMailForm;
use AcMarche\Courrier\Filament\Resources\IncomingMailResource\Tables\IncomingMailTables;
use AcMarche\Courrier\Models\IncomingMail;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class IncomingMailResource extends Resource
{
    protected static ?string $model = IncomingMail::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
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
            'index' => Pages\ListIncomingMails::route('/'),
            'create' => Pages\CreateIncomingMail::route('/create'),
            'view' => Pages\ViewIncomingMail::route('/{record}/view'),
            'edit' => Pages\EditIncomingMail::route('/{record}/edit'),
        ];
    }
}
