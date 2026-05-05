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
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use Override;

final class ManagerValidateTelework extends EditRecord
{
    #[Override]
    protected static string $resource = TeleworkResource::class;

    #[Override]
    protected static ?string $title = 'Validation du directeur';

    public static function canAccess(array $parameters = []): bool
    {
        return Gate::forUser(auth()->user())->check('hrm-director');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Validation du directeur - '.$this->record->user_add;
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return TeleworkForm::validationService($schema);
    }

    protected function afterSave(): void
    {
        /** @var Telework $telework */
        $telework = $this->record;

        TeleworkNotifier::notifyEmployeeAfterManagerValidation($telework);

        if ($telework->manager_validated) {
            TeleworkNotifier::notifyHrTeam($telework);
        }

        Notification::make()
            ->title('Validation enregistrée et notifications envoyées')
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
            $this->getSaveFormAction()->label('Enregistrer la validation'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour à la demande')
                ->icon(Heroicon::ArrowLeft)
                ->url(fn() => ViewTelework::getUrl(['record' => $this->record])),
        ];
    }
}
