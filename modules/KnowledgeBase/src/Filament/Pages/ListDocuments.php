<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Filament\Pages;

use AcMarche\KnowledgeBase\Documentation\Documentation;
use AcMarche\KnowledgeBase\KnowledgeBaseAccess;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;

final class ListDocuments extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedBookOpen;

    #[Override]
    protected static ?string $navigationLabel = 'Documentation';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $slug = 'docs';

    #[Override]
    protected string $view = 'knowledge-base::filament.pages.list-documents';

    public static function canAccess(): bool
    {
        return KnowledgeBaseAccess::canRead(auth()->user());
    }

    public function getTitle(): string
    {
        return 'Documentation';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (?string $search, ?string $sortColumn, ?string $sortDirection): Collection => $this->documents($search, $sortColumn, $sortDirection))
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->weight(FontWeight::SemiBold)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modifié')
                    ->since()
                    ->sortable(),
            ])
            ->searchable()
            ->defaultSort('title')
            ->recordUrl(fn (array $record): string => ShowDocument::getUrl(['document' => $record['slug']]))
            ->emptyStateHeading('Aucune page')
            ->emptyStateDescription('La base de connaissances est vide.');
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
