<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\App\Filament\Schemas\ClaimRequestForm;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use NumberFormatter;
use Override;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class ClaimRequestPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-home-modern';

    #[Override]
    protected static ?string $navigationLabel = 'Déclaration de créance';

    #[Override]
    protected string $view = 'app::filament.pages.claim-request';

    public function getTitle(): string
    {
        return 'Générer une déclaration de créance';
    }

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        $data = [];
        $data['last_name'] = $user->last_name;
        $data['first_name'] = $user->first_name;

        if ($personalInformation = PersonalInformationRepository::getByCurrentUser()->first()) {
            $data['iban'] = $personalInformation->iban;
            $data['street'] = $personalInformation->street;
            $data['postal_code'] = $personalInformation->postal_code;
            $data['city'] = $personalInformation->city;
        }

        $this->form->fill($data ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return ClaimRequestForm::configure($schema)->statePath('data');
    }

    public function save(): StreamedResponse
    {
        $data = $this->form->getState();
        $amount = (float) $data['amount'];

        $viewData = [
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
            'street' => $data['street'],
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
            'iban' => $data['iban'],
            'content' => $data['content'],
            'amount' => number_format($amount, 2, ',', '.'),
            'amount_in_words' => $this->amountInWords($amount),
            'filing_date' => CarbonImmutable::parse($data['filing_date'])->format('d-m-Y'),
        ];

        $filename = sprintf('declaration-de-creance-%s.pdf', now()->format('Y-m-d'));

        $pdf = pdf()
            ->view('app::pdf.claim-request', $viewData)
            ->withBrowsershot(function (Browsershot $browsershot): void {
                if ($path = config('pdf.node_modules_path')) {
                    $browsershot->setNodeModulePath($path);
                }
                if ($path = config('pdf.chrome_path')) {
                    $browsershot->setChromePath($path);
                }
            })
            ->name($filename)
            ->download();

        return response()->streamDownload(
            function () use ($pdf): void {
                echo $pdf->toResponse(request())->getContent();
            },
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Spell out the given amount in French (e.g. 1234.5 => "mille deux cent
     * trente-quatre virgule cinq").
     */
    private function amountInWords(float $amount): string
    {
        $formatter = new NumberFormatter('fr_BE', NumberFormatter::SPELLOUT);

        return (string) $formatter->format($amount);
    }
}
