<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\FollowUp\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class FollowUpForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('content')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('icon')
                    ->label('Icône')
                    ->disk('public')
                    ->directory(config('pst.uploads.followups_icons')),
            ]);
    }
}
