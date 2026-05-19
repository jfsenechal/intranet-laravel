<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Agendas;

use AcMarche\Conseil\Filament\Resources\Agendas\Pages\CreateAgenda;
use AcMarche\Conseil\Filament\Resources\Agendas\Pages\EditAgenda;
use AcMarche\Conseil\Filament\Resources\Agendas\Pages\ListAgendas;
use AcMarche\Conseil\Filament\Resources\Agendas\Pages\ViewAgenda;
use AcMarche\Conseil\Filament\Resources\Agendas\Schemas\AgendaForm;
use AcMarche\Conseil\Filament\Resources\Agendas\Tables\AgendasTable;
use AcMarche\Conseil\Models\Agenda;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class AgendaResource extends Resource
{
    #[Override]
    protected static ?string $model = Agenda::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Ordres du jour';

    #[Override]
    protected static ?string $modelLabel = 'ordre du jour';

    #[Override]
    protected static ?string $pluralModelLabel = 'ordres du jour';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return AgendaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgendasTable::configure($table);
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
            'index' => ListAgendas::route('/'),
            'create' => CreateAgenda::route('/create'),
            'view' => ViewAgenda::route('/{record}'),
            'edit' => EditAgenda::route('/{record}/edit'),
        ];
    }
}
