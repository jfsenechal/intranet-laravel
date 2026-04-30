<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Odd\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OddForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                FileUpload::make('icon')
                    ->label('Icône')
                    ->disk('public')
                    ->directory(config('pst.uploads.odds_icons'))
                    ->previewable(false)
                    ->maxFiles(1)
                    ->image(),
                Section::make()->schema([
                    ColorPicker::make('color')
                        ->label('Couleur'),
                    TextInput::make('position')
                        ->required()
                        ->numeric(),
                ]),
                Textarea::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
