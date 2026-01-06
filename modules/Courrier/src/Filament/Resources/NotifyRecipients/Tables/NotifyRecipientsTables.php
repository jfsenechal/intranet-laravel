<?php

namespace AcMarche\Courrier\Filament\Resources\NotifyRecipients\Tables;

use AcMarche\Courrier\Models\IncomingMail;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotifyRecipientsTables
{
    public static function configure(Table $table, ?string $mailDate): Table
    {
        return $table
            ->query(
                IncomingMail::query()
                    ->where('is_notified', false)
                    ->when($mailDate, function (Builder $query) use ($mailDate): void {
                        $query->whereDate('mail_date', $mailDate);
                    })
                    ->with(['services', 'recipients', 'attachments', 'category'])
            )
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Numero')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sender')
                    ->label('Expediteur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('mail_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('primaryRecipients')
                    ->label('Destinataires principaux')
                    ->state(function (IncomingMail $record): string {
                        $services = $record->services->where('pivot.is_primary', true)->pluck('name');
                        $recipients = $record->recipients->where('pivot.is_primary', true)->map(
                            fn($r) => "{$r->first_name} {$r->last_name}"
                        );

                        return $services->merge($recipients)->implode(', ');
                    }),
                IconColumn::make('is_registered')
                    ->label('Recommande')
                    ->boolean(),
                IconColumn::make('has_acknowledgment')
                    ->label('Accuse')
                    ->boolean(),
                TextColumn::make('attachments_count')
                    ->label('Pieces jointes')
                    ->counts('attachments'),
            ])
            ->paginated([10, 25, 50]);

    }
}
