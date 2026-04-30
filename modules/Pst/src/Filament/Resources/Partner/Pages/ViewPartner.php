<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Partner\Pages;

use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Pst\Filament\Resources\Partner\PartnerResource;
use AcMarche\Pst\Models\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Override;

final class ViewPartner extends ViewRecord
{
    #[Override]
    protected static string $resource = PartnerResource::class;

    public function getTitle(): string
    {
        return $this->record->name.' '.$this->record->initials ?? 'Empty name';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('email')
                    ->icon('tabler-mail'),
                TextEntry::make('phone')
                    ->icon('tabler-phone'),
                TextEntry::make('description')
                    ->label(null)
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
                Fieldset::make('actions')
                    ->label('Actions liés')
                    ->schema([
                        RepeatableEntry::make('actions')
                            ->label(null)
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nom')
                                    ->columnSpanFull()
                                    ->url(
                                        fn (Action $record): string => ActionPstResource::getUrl(
                                            'view',
                                            ['record' => $record]
                                        )
                                    ),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
