<?php

declare(strict_types=1);

namespace AcMarche\Note\Filament\Resources\Notes;

use AcMarche\Note\Filament\Resources\Notes\Pages\CreateNote;
use AcMarche\Note\Filament\Resources\Notes\Pages\EditNote;
use AcMarche\Note\Filament\Resources\Notes\Pages\ListNotes;
use AcMarche\Note\Filament\Resources\Notes\Pages\ViewNote;
use AcMarche\Note\Filament\Resources\Notes\Schemas\NoteForm;
use AcMarche\Note\Filament\Resources\Notes\Schemas\NoteInfolist;
use AcMarche\Note\Filament\Resources\Notes\Tables\NoteTables;
use AcMarche\Note\Models\Note;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;

final class NoteResource extends Resource
{
    #[Override]
    protected static ?string $model = Note::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-document-text';

    #[Override]
    protected static ?string $navigationLabel = 'Notes';

    #[Override]
    protected static ?string $modelLabel = 'Note';

    #[Override]
    protected static ?string $pluralModelLabel = 'Notes';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return NoteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NoteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoteTables::configure($table);
    }

    /**
     * Notes are private to their author: every page of this resource, route model
     * binding included, only ever resolves notes whose `user_add` matches the
     * current user. NotePolicy enforces the same rule per record.
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_add', Auth::user()?->username);
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
            'index' => ListNotes::route('/'),
            'create' => CreateNote::route('/create'),
            'view' => ViewNote::route('/{record}'),
            'edit' => EditNote::route('/{record}/edit'),
        ];
    }
}
