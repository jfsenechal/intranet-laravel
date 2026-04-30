<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Signatures\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class SignatureTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->label('Prénom'),
                TextColumn::make('last_name')->label('Nom'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('job_title')->label('Fonction')->toggleable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class);
    }
}
