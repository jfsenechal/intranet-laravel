<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Pages;

use AcMarche\Hrm\Filament\Resources\Teleworks\Schemas\TeleworkForm;
use AcMarche\Hrm\Filament\Resources\Teleworks\TeleworkResource;
use AcMarche\Hrm\Models\Telework;
use AcMarche\Hrm\Services\TeleworkNotifier;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use Override;

final class HrValidateTelework extends EditRecord
{
    #[Override]
    protected static string $resource = TeleworkResource::class;

    #[Override]
    protected static ?string $title = 'Traitement GRH';

    public static function canAccess(array $parameters = []): bool
    {
        return Gate::forUser(auth()->user())->check('hrm-administrator');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Traitement GRH - '.$this->record->user_add;
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return TeleworkForm::validationGrh($schema);
    }

    protected function afterSave(): void
    {
        /** @var Telework $telework */
        $telework = $this->record;

        TeleworkNotifier::notifyEmployeeAfterHrValidation($telework);

        Notification::make()
            ->title('Traitement enregistré et agent notifié')
            ->success()
            ->send();
    }

    #[Override]
    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Enregistrer'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour à la demande')
                ->url(fn () => ViewTelework::getUrl(['record' => $this->record])),
        ];
    }
}
