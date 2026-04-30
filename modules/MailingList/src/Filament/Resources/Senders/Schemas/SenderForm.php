<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Senders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

final class SenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->maxLength(255)
                    ->required()
                    ->columnSpanFull(),
                Flex::make([
                    TextInput::make('email')
                        ->label('E-mail')
                        ->helperText('Seules les adresses @marche.be, @ac.marche.be, @cpas.marche.be sont autorisées.')
                        ->email()
                        ->maxLength(255)
                        ->required()
                        ->rules(['regex:/^.+@(marche\.be|ac\.marche\.be|cpas\.marche\.be)$/i']),
                    FileUpload::make('logo')
                        ->label('Logo')
                        ->helperText('Le logo sera joint au mail')
                        ->image()
                        ->disk('public')
                        ->directory(config('mailing-list.uploads.senders_logos'))
                        ->visibility('public')
                        ->automaticallyResizeImagesMode('cover')
                       // ->imageAspectRatio('16:9')
                        ->automaticallyResizeImagesToWidth('300')
                        ->columnSpanFull(),
                ])
                    ->columnSpanFull(),
                RichEditor::make('footer')
                    ->label('Pied de page')
                    ->helperText('Il sera affiché dans le pied page.')
                    ->columnSpanFull(),
                Hidden::make('username')
                    ->default(fn (): ?string => auth()->user()?->username),
            ]);
    }
}
