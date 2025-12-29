<?php

namespace AcMarche\Mileage\Filament\Pages;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Handler\ExportHandler;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPdf\PdfBuilder;
use UnitEnum;

final class AnnualExport extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public bool $searched = false;

    /** @var Collection<int, array<string, mixed>> */
    public Collection $declarations;

    public int $totalKilometers = 0;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Export annuel';

    protected static string|null|UnitEnum $navigationGroup = 'Administration';

    protected string $view = 'mileage::filament.pages.annual-export';

    public static function getNavigationIcon(): ?string
    {
        return 'tabler-file-export';
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
        return 'Export annuel des déclarations';
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->declarations = collect();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                TextInput::make('year')
                    ->label('Année')
                    ->numeric()
                    ->required()
                    ->autocomplete(false),
                Select::make('department')
                    ->label('Département')
                    ->options(DepartmentEnum::class)
                    ->required()
                    ->native(false),
                Radio::make('omnium')
                    ->label('Omnium')
                    ->options([
                        '1' => 'Oui',
                        '0' => 'Non',
                    ])
                    ->required()
                    ->inline(),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $data = $this->form->getState();

        $department = $data['department'];
        $year = (int) $data['year'];
        $omnium = $data['omnium'] === '1';

        $exportHandler = new ExportHandler();
        $result = $exportHandler->byYear($year, [$department], $omnium);

        $this->declarations = $result['declarations'];
        $this->totalKilometers = $result['totalKilometers'];
        $this->searched = true;
    }

    public function downloadPdf(): PdfBuilder
    {
        $data = $this->form->getState();

        $department = $data['department'];
        $year = (int) $data['year'];
        $omnium = $data['omnium'] === '1';

        $exportHandler = new ExportHandler();

        return $exportHandler->exportByYearPdf($year, [$department], $omnium);
    }
}
