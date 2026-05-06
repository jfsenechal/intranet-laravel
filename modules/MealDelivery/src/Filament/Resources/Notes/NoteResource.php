<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes;

use AcMarche\MealDelivery\Filament\Resources\Notes\Pages\CreateNote;
use AcMarche\MealDelivery\Filament\Resources\Notes\Pages\EditNote;
use AcMarche\MealDelivery\Filament\Resources\Notes\Pages\ListNotes;
use AcMarche\MealDelivery\Filament\Resources\Notes\Schemas\NoteForm;
use AcMarche\MealDelivery\Filament\Resources\Notes\Tables\NoteTables;
use AcMarche\MealDelivery\Models\Note;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class NoteResource extends Resource
{
    #[Override]
    protected static ?string $model = Note::class;

    #[Override]
    protected static ?int $navigationSort = 7;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Client notes';
    }

    public static function form(Schema $schema): Schema
    {
        return NoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoteTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotes::route('/'),
            'create' => CreateNote::route('/create'),
            'edit' => EditNote::route('/{record}/edit'),
        ];
    }
}
