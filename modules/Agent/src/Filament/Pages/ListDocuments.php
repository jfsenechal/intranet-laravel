<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Pages;

use AcMarche\Agent\Documentation\Documentation;
use AcMarche\Agent\Filament\Tables\DocumentTables;
use AcMarche\Agent\KnowledgeBaseAccess;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use UnitEnum;

final class ListDocuments extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedBookOpen;

    #[Override]
    protected static ?string $navigationLabel = 'Base de connaissances';

    #[Override]
    protected static ?string $title = 'Base de connaissances';

    protected static string|UnitEnum|null $navigationGroup = 'Documentations';

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $slug = 'docs';

    #[Override]
    protected string $view = 'agent::filament.pages.list-documents';

    public static function canAccess(): bool
    {
        return KnowledgeBaseAccess::canRead(auth()->user());
    }

    public function table(Table $table): Table
    {
        return DocumentTables::configure($table, fn (?string $search, ?string $sortColumn, ?string $sortDirection): Collection => $this->documents($search, $sortColumn, $sortDirection));
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Nouvelle page')
                ->icon(Heroicon::Plus)
                ->visible(KnowledgeBaseAccess::canWrite(auth()->user()))
                ->url(CreateDocument::getUrl()),
        ];
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    private function documents(?string $search, ?string $sortColumn, ?string $sortDirection): Collection
    {
        $documents = app(Documentation::class)->all();

        if (filled($search)) {
            $documents = $documents->filter(
                fn (array $document): bool => Str::contains($document['title'], $search, ignoreCase: true)
                    || Str::contains($document['content'], $search, ignoreCase: true),
            );
        }

        $sortColumn = in_array($sortColumn, ['title', 'updated_at'], true) ? $sortColumn : 'title';

        return $documents->sortBy(
            fn (array $document): string => (string) $document[$sortColumn],
            SORT_NATURAL | SORT_FLAG_CASE,
            descending: $sortDirection === 'desc',
        );
    }
}
