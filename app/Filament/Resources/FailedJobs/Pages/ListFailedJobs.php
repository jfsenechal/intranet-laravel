<?php

declare(strict_types=1);

namespace App\Filament\Resources\FailedJobs\Pages;

use App\Filament\Resources\FailedJobs\FailedJobResource;
use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Override;

final class ListFailedJobs extends ListRecords
{
    #[Override]
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryAll')
                ->label('Tout relancer')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Relancer tous les jobs échoués ?')
                ->visible(fn (): bool => FailedJob::query()->exists())
                ->action(function (): void {
                    Artisan::call('queue:retry', ['id' => ['all']]);

                    Notification::make()
                        ->title('Tous les jobs échoués ont été remis en file d\'attente')
                        ->success()
                        ->send();
                }),
            Action::make('flush')
                ->label('Tout vider')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Vider la liste des jobs échoués ?')
                ->modalDescription('Tous les enregistrements seront supprimés définitivement.')
                ->visible(fn (): bool => FailedJob::query()->exists())
                ->action(function (): void {
                    Artisan::call('queue:flush');

                    Notification::make()
                        ->title('Liste des jobs échoués vidée')
                        ->success()
                        ->send();
                }),
        ];
    }
}
