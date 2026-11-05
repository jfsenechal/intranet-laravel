<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Models\Citoyen;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

final class CitoyenQuotaAction
{
    public static function make(Model|Citoyen $record): Action
    {
        return Action::make('changeQuota')
            ->label('Changer le quota')
            ->icon(Heroicon::CircleStack)
            ->color('info')
            ->authorize('update', $record)
            ->modalHeading("Quota de {$record->mail}")
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(fn (): array => ['quota_bytes' => $record->virtualUser->quota_bytes])
            ->schema([
                TextInput::make('quota_bytes')
                    ->label('Nouveau quota')
                    ->helperText('Minimum 150 Mo, maximum 4096 Mo (4 Go).')
                    ->integer()
                    ->required()
                    ->minValue(150)
                    ->maxValue(4096)
                    ->suffix('Mo'),
            ])
            ->action(function (array $data) use ($record): void {
                $record->virtualUser->update(['quota_bytes' => (int) $data['quota_bytes']]);

                Notification::make()
                    ->title('Le quota a bien été modifié')
                    ->success()
                    ->send();
            });
    }
}
