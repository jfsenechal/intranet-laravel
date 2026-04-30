<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Schemas;

use AcMarche\Agent\Models\Profile;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

final class ProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identité')
                    ->columns(12)
                    ->schema([
                        ImageEntry::make('photo')
                            ->label('Photo')
                            ->disk('public')
                            ->imageHeight(260)
                            ->defaultImageUrl(
                                fn (Profile $record
                                ): string => 'https://ui-avatars.com/api/?size=256&name='.urlencode(
                                    mb_trim($record->first_name.' '.$record->last_name)
                                )
                            )
                            ->columnSpan(3),
                        Fieldset::make('Coordonnées')
                            ->columns(2)
                            ->columnSpan(9)
                            ->schema([
                                TextEntry::make('username')->label('Identifiant')->copyable(),
                                TextEntry::make('employee_id')->label('Matricule RH'),
                                TextEntry::make('location')->label('Emplacement'),
                                IconEntry::make('no_mail')->label('Pas de mail professionnel nécessaire')
                                    ->visible(fn (Model $record) => $record->no_mail === true),
                                TextEntry::make('notes')->label('Remarques')->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Accès')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('emails')
                            ->label('Mailboxes partagées')
                            ->listWithLineBreaks(),
                        TextEntry::make('supervisors')
                            ->label('Responsables')
                            ->listWithLineBreaks(),
                    ]),
                Grid::make(2)->schema([
                    Section::make('Matériel')
                        ->relationship('hardware')
                        ->schema([
                            TextEntry::make('existing_pc')->label('PC existant'),
                            TextEntry::make('new_pc')->label('Nouveau PC'),
                            IconEntry::make('vpn')->label('VPN'),
                            TextEntry::make('other')->label('Autre'),
                        ]),
                    Section::make('Téléphonie')
                        ->relationship('phone')
                        ->schema([
                            TextEntry::make('existing_number')->label('Numéro existant'),
                            TextEntry::make('mobile_number')->label('Numéro mobile'),
                            IconEntry::make('new_number')->label('Nouveau numéro'),
                            IconEntry::make('external_number')->label('Numéro extérieur'),
                        ]),
                ]),
            ]);
    }
}
