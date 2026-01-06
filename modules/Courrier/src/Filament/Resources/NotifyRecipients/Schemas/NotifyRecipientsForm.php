<?php

namespace AcMarche\Courrier\Filament\Resources\NotifyRecipients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class NotifyRecipientsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('mail_date')
                    ->label('Date du courrier')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadPreviewData()),
            ]);
    }
}
