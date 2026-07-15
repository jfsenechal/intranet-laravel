<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas\IncomingMailForm;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Tables\IncomingMailTables;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Search\MeiliSearcher;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class IncomingMailSearch extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Ids returned by the last Meilisearch query, in relevance order.
     * Null until a first search is run.
     *
     * @var array<int, int>|null
     */
    public ?array $resultIds = null;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-search';

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Rechercher';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    #[Override]
    protected string $view = 'courrier::filament.pages.incoming-mail-search';

    public function getTitle(): string
    {
        return 'Recherche avancée';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return IncomingMailForm::forAdvanceSearch($schema);
    }

    public function search(): void
    {
        $state = $this->form->getState();

        $this->resultIds = app(MeiliSearcher::class)->searchIds(
            (string) ($state['query'] ?? ''),
            Auth::user(),
            [
                'reference' => $state['reference'] ?? null,
                'category' => $state['category'] ?? null,
                'date_from' => filled($state['date_from'] ?? null) ? Carbon::parse($state['date_from']) : null,
                'date_to' => filled($state['date_to'] ?? null) ? Carbon::parse($state['date_to']) : null,
                'services' => $state['services'] ?? [],
                'destinataires' => $state['destinataires'] ?? [],
                'department' => $state['department'] ?? null,
            ],
        );

        $this->resetTable();
    }

    public function resetSearch(): void
    {
        $this->form->fill();
        $this->resultIds = null;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return IncomingMailTables::forAdvanceSearch($table, $this->getTableQuery());
    }

    protected function getTableQuery(): Builder
    {
        $query = IncomingMail::query()->with(['services', 'recipients']);

        $ids = $this->resultIds ?? [];
        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, '.implode(',', $ids).')');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('search')
                ->label('Rechercher')
                ->icon('tabler-search')
                ->action('search'),
            Action::make('reset')
                ->label('Réinitialiser')
                ->color('gray')
                ->action('resetSearch'),
        ];
    }
}
