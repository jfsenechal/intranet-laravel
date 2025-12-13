<?php

namespace AcMarche\Security\Filament\Resources\Users\Pages;

use AcMarche\Security\Filament\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

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
        return $this->record->fullName() ?? 'Empty name';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
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
