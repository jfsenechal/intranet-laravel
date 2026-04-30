<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Widgets;

use AcMarche\MailingList\Filament\Resources\Emails\Tables\EmailsTable;
use AcMarche\MailingList\Models\Email;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class LastCampaignsWidget extends TableWidget
{
    #[Override]
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return EmailsTable::configure($table)
            ->query(fn (): Builder => Email::query())
            ->defaultPaginationPageOption(5)
            ->heading('Vos dernières campagnes')
            ->description('Comptes dont la dernière connexion date de plus de 2 ans ou sans aucune connexion.');
    }
}
