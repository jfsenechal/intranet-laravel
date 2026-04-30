<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Pages;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Filament\Resources\QrCodes\Schemas\QrCodeForm;
use AcMarche\QrCode\Models\QrCode;
use AcMarche\QrCode\Service\QrCodeGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GenerateQrCode extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

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

    public function mount(): void
    {
        $this->form->fill([
            'action' => QrCodeActionEnum::URL->value,
            'color' => '#000000',
            'background_color' => '#FFFFFF',
            'format' => 'SVG',
            'style' => 'square',
            'pixels' => 400,
            'margin' => 10,
            'save_to_db' => false,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                ...QrCodeForm::configure($schema)->getComponents(),
                Section::make('Enregistrement')
                    ->schema([
                        Toggle::make('save_to_db')
                            ->label('Enregistrer dans mes QR codes')
                            ->helperText('Cochez pour conserver ce QR code dans la base de données.')
                            ->default(false),
                    ]),
            ]);
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

        if (! empty($data['save_to_db'])) {
            $qrCode->user_id = auth()->id();
            $qrCode->username = auth()->user()?->name;
            $qrCode->save();

            Notification::make()
                ->success()
                ->title('QR code enregistré')
                ->body('Votre QR code a été ajouté à votre collection.')
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('QR code généré')
                ->send();
        }
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
                ->action(fn () => $this->mount()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function makeModel(array $data): QrCode
    {
        unset($data['save_to_db']);

        $qrCode = new QrCode();
        $qrCode->fill($data);
        $qrCode->name = $data['name'] ?? 'QR code';
        $qrCode->action = $data['action'] instanceof QrCodeActionEnum
            ? $data['action']
            : QrCodeActionEnum::from((string) $data['action']);

        return $qrCode;
    }
}
