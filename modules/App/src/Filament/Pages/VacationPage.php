<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\App\Filament\Schemas\VacationForm;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class VacationPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::PaperAirplane;

    #[Override]
    protected static ?string $navigationLabel = 'Absence de bureau';

    #[Override]
    protected string $view = 'app::filament.pages.vacation';

    public function getTitle(): string
    {
        return 'Absence de bureau';
    }

    public function form(Schema $schema): Schema
    {
        return VacationForm::configure($schema)->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Notification::make()
            ->title('Enregistré')
            ->success()
            ->send();
    }
}
