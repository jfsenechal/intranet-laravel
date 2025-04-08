<?php

namespace AcMarche\Security\Filament\Resources\UserResource\Pages;

use AcMarche\Security\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->name() ?? 'Empty name';
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('first_name')
                ->label('Prénom'),
            TextEntry::make('last_name')
                ->label('Nom'),
            TextEntry::make('email')
                ->label('Email')
                ->icon('tabler-mail'),
            TextEntry::make('phone')
                ->label('Téléphone')
                ->icon('tabler-phone'),
            TextEntry::make('mobile')
                ->label('Mobile')
                ->icon('tabler-device-mobile'),
            TextEntry::make('extension')
                ->label('Extension')
                ->icon('tabler-device-landline-phone'),
        ]);
    }

}
