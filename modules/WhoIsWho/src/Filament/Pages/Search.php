<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Pages;

use AcMarche\Hrm\Models\Employee;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Override;

final class Search extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public ?string $term = null;

    /**
     * @var Collection<int, Employee>
     */
    public Collection $results;

    public bool $searched = false;

    #[Override]
    protected string $view = 'who-is-who::filament.pages.search';

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Rechercher';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-magnifying-glass';
    }

    public function getTitle(): string
    {
        return 'Rechercher un agent';
    }

    public function mount(): void
    {
        $this->results = new Collection();
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('term')
                    ->label('Nom, prénom ou service')
                    ->placeholder('Tapez au moins 2 caractères...')
                    ->autofocus()
                    ->required()
                    ->minLength(2),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $data = $this->form->getState();
        $this->term = $data['term'] ?? null;
        $this->results = EmployeeRepository::search($this->term);
        $this->searched = true;
    }
}
