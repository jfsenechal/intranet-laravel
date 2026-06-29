<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Search\MeiliSearcher;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('query')
                            ->label('Recherche plein texte')
                            ->placeholder('Référence, expéditeur, description…')
                            ->columnSpanFull(),
                        DatePicker::make('date_from')
                            ->label('Du')
                            ->native(false),
                        DatePicker::make('date_to')
                            ->label('Au')
                            ->native(false),
                        Select::make('services')
                            ->label('Services')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => Service::query()->orderBy('name')->pluck('name', 'id')->all()),
                        Select::make('destinataires')
                            ->label('Destinataires')
                            ->multiple()
                            ->searchable()
                            ->options(fn (): array => Recipient::query()
                                ->orderBy('last_name')
                                ->get()
                                ->pluck('full_name', 'id')
                                ->all()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $state = $this->form->getState();

        $this->resultIds = app(MeiliSearcher::class)->searchIds(
            (string) ($state['query'] ?? ''),
            Auth::user(),
            [
                'date_from' => filled($state['date_from'] ?? null) ? Carbon::parse($state['date_from']) : null,
                'date_to' => filled($state['date_to'] ?? null) ? Carbon::parse($state['date_to']) : null,
                'services' => $state['services'] ?? [],
                'destinataires' => $state['destinataires'] ?? [],
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
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->paginated([25, 50, 100])
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Référence'),
                TextColumn::make('mail_date')
                    ->date('d/m/Y')
                    ->label('Date'),
                TextColumn::make('sender')
                    ->label('Expéditeur'),
                TextColumn::make('description')
                    ->label('Description')
                    ->html()
                    ->limit(80),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),
                TextColumn::make('recipients.full_name')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),
                IconColumn::make('is_registered')
                    ->label('Recom')
                    ->boolean(),
            ])
            ->recordUrl(fn (IncomingMail $record): string => IncomingMailResource::getUrl('view', ['record' => $record]));
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
