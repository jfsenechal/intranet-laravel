<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

final class RevokeAction
{
    public static function make(): Action
    {
        return Action::make('revoke')
            ->label('Révoquer')
            ->icon(Heroicon::UserMinus)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Révoquer l\'accès');
    }
}
