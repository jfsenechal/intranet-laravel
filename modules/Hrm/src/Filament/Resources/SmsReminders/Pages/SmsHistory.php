<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Pages;

use AcMarche\App\Sms\Exception\SmsException;
use AcMarche\App\Sms\InforiusClient;
use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use BackedEnum;
use DateTimeImmutable;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;
use Throwable;

final class SmsHistory extends Page
{
    #[Override]
    protected static string $resource = SmsReminderResource::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedClock;

    #[Override]
    protected string $view = 'hrm::filament.sms-reminders.history';

    public function getTitle(): string|Htmlable
    {
        return 'Historique des SMS envoyés';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        try {
            $response = InforiusClient::fromConfig()
                ->getHistory(new DateTimeImmutable('-2 months'));
        } catch (SmsException|Throwable $exception) {
            return [
                'lines' => [],
                'error' => $exception->getMessage(),
            ];
        }

        return [
            'lines' => $response->lines,
            'error' => $response->error,
        ];
    }
}
