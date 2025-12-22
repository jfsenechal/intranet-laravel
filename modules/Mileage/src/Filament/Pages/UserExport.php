<?php

namespace AcMarche\Mileage\Filament\Pages;

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Handler\ExportHandler;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Repository\DeclarationRepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPdf\PdfBuilder;
use UnitEnum;

final class UserExport extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public bool $searched = false;

    public ?Declaration $declaration = null;

    /** @var array<int, string> */
    public array $months = [];

    /** @var array<int> */
    public array $years = [];

    /** @var array{interne: array<int, array<int, int>>, externe: array<int, array<int, int>>} */
    public array $trips = [];

    public string $selectedUsername = '';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Export par utilisateur';

    protected static string|null|UnitEnum $navigationGroup = 'Administration';

    protected string $view = 'mileage::filament.pages.user-export';

    public static function getNavigationIcon(): ?string
    {
        return 'tabler-user-check';
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();
        if ($user?->isAdministrator()) {
            return true;
        }

        return $user?->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value) ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Export des déclarations par utilisateur';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Select::make('username')
                    ->label('Utilisateur')
                    ->options(DeclarationRepository::getAllUsernames())
                    ->required()
                    ->searchable()
                    ->native(false),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $data = $this->form->getState();

        $username = $data['username'];
        $this->selectedUsername = $username;

        $exportHandler = new ExportHandler();
        $result = $exportHandler->byUser($username);

        $this->declaration = $result['declaration'];
        $this->months = $result['months'];
        $this->years = $result['years'];
        $this->trips = $result['deplacements'];
        $this->searched = true;
    }

    public function downloadPdf(): PdfBuilder
    {
        $data = $this->form->getState();
        $username = $data['username'];

        $exportHandler = new ExportHandler();

        return $exportHandler->exportByUserPdf($username);
    }
}
