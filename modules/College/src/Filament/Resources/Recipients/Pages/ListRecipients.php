<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Pages;

use AcMarche\College\Filament\Resources\Recipients\RecipientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Override;

final class ListRecipients extends ListRecords
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    protected static ?string $title = 'Liste des destinataires';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('Conditions de notification')
                    ->icon(Heroicon::InformationCircle)
                    ->info()
                    ->description(new HtmlString(
                        'Pour qu\'un destinataire soit notifié d\'un <strong>ORDRE</strong>, il doit être coché dans au moins une des cases "Ordre du jour Service" ou "Ordre du jour Collège".<br><br>'.
                        'Pour qu\'un destinataire soit notifié d\'un <strong>PV</strong>, il doit être coché dans au moins une des cases "PV Service" ou "PV Collège".'
                    )),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau destinataire')
                ->icon(Heroicon::Plus),
        ];
    }
}
