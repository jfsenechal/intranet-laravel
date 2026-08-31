<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Pages;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Filament\Resources\QrCodes\Schemas\QrCodeForm;
use AcMarche\QrCode\Models\QrCode;
use AcMarche\QrCode\Service\QrCodeGenerator;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GenerateQrCode extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

    /**
     * Kept as a Livewire property: the `action` query string is only present on the initial page
     * load, not on the subsequent Livewire update requests (live fields, generate, download).
     */
    public ?string $action = null;

    /**
     * True when the action came from ChooseQrCodeAction: the select is then replaced by a hidden
     * field, the choice having already been made on the previous page.
     */
    public bool $isActionLocked = false;

    /**
     * The record saved by the last generation: regenerating from the same page updates it instead
     * of piling up a new QR code in the user's collection on every click.
     */
    public ?int $qrCodeId = null;

    public ?string $previewMarkup = null;

    public ?string $previewMime = null;

    protected string $view = 'qrcode::filament.pages.generate-qr-code';

    protected static ?string $title = 'Générer un QR code';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-qr-code';
    }

    public static function getNavigationLabel(): string
    {
        return 'Générer un QR code';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $chosenAction = QrCodeActionEnum::tryFrom((string) request()->query('action'));

        if ($this->action === null) {
            $this->action = $chosenAction?->value ?? QrCodeActionEnum::URL->value;
            $this->isActionLocked = $chosenAction instanceof QrCodeActionEnum;
        }

        $this->form->fill([
            'action' => $this->action,
            'color' => '#000000',
            'background_color' => '#FFFFFF',
            'format' => 'SVG',
            'style' => 'square',
            'pixels' => 400,
            'margin' => 10,
        ]);
    }

    public function getTitle(): string
    {
        $action = QrCodeActionEnum::tryFrom((string) $this->action);

        return $action instanceof QrCodeActionEnum
            ? 'Générer un QR code : '.mb_lcfirst($action->getLabel())
            : 'Générer un QR code';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components(
                QrCodeForm::configure($schema, isActionSelectable: ! $this->isActionLocked)->getComponents(),
            );
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $qrCode = $this->makeModel($data);
        $generator = app(QrCodeGenerator::class);
        $content = $generator->render($qrCode);

        $mime = $generator->mimeType($qrCode);

        if (mb_strtolower($qrCode->format ?? 'svg') === 'svg') {
            $this->previewMarkup = $content;
        } else {
            $this->previewMarkup = sprintf(
                '<img src="data:%s;base64,%s" alt="QR Code" class="max-w-full h-auto" />',
                $mime,
                base64_encode($content),
            );
        }
        $this->previewMime = $mime;

        $qrCode->user_id = auth()->id();
        $qrCode->username = auth()->user()?->username;
        $qrCode->save();

        $this->qrCodeId = $qrCode->id;

        Notification::make()
            ->success()
            ->title('QR code enregistré')
            ->body('Votre QR code a été ajouté à votre collection.')
            ->send();
    }

    public function downloadAction(): Action
    {
        return Action::make('download')
            ->label('Télécharger')
            ->icon('heroicon-o-arrow-down-tray')
            ->visible(fn (): bool => $this->previewMarkup !== null)
            ->action(function (): StreamedResponse {
                $data = $this->form->getState();
                $qrCode = $this->makeModel($data);
                $generator = app(QrCodeGenerator::class);
                $content = $generator->render($qrCode);
                $filename = Str::slug($qrCode->name ?: 'qrcode').'.'.$generator->extension($qrCode);

                return response()->streamDownload(
                    fn () => print $content,
                    $filename,
                    ['Content-Type' => $generator->mimeType($qrCode)],
                );
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changeAction')
                ->label('Changer d\'action')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(ChooseQrCodeAction::getUrl()),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate')
                ->label('Générer')
                ->icon('heroicon-o-bolt')
                ->submit('generate'),
            Action::make('reset')
                ->label('Réinitialiser')
                ->color('gray')
                ->action(function (): void {
                    $this->qrCodeId = null;
                    $this->previewMarkup = null;
                    $this->previewMime = null;
                    $this->mount();
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function makeModel(array $data): QrCode
    {
        $qrCode = QrCode::query()
            ->where('user_id', auth()->id())
            ->find($this->qrCodeId) ?? new QrCode();
        $qrCode->fill($data);
        $qrCode->name = $data['name'] ?? 'QR code';
        $qrCode->action = $data['action'] instanceof QrCodeActionEnum
            ? $data['action']
            : QrCodeActionEnum::from((string) $data['action']);

        return $qrCode;
    }
}
