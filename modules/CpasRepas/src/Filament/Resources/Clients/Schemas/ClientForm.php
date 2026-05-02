<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Personal information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('salutation')
                                    ->label('Salutation')
                                    ->options([
                                        'M.' => 'M.',
                                        'Mme' => 'Mme',
                                        'Dr' => 'Dr',
                                    ])
                                    ->nullable()
                                    ->columnSpan(1),

                                TextInput::make('last_name')
                                    ->label('Last name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('first_name')
                                    ->label('First name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),

                        DatePicker::make('birth_date')
                            ->label('Birth date')
                            ->nullable(),
                    ]),

                Section::make('Address')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('street')
                                    ->label('Street')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                TextInput::make('number')
                                    ->label('Number')
                                    ->required()
                                    ->maxLength(20)
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('postal_code')
                                    ->label('Postal code')
                                    ->required()
                                    ->numeric()
                                    ->columnSpan(1),

                                TextInput::make('city')
                                    ->label('City')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ]),

                        TextInput::make('floor')
                            ->label('Floor / Apartment')
                            ->maxLength(100)
                            ->nullable(),
                    ]),

                Section::make('Contact')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),

                        TextInput::make('contact_name')
                            ->label('Emergency contact name')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('contact_phone')
                            ->label('Emergency contact phone')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),

                        Textarea::make('contact_notes')
                            ->label('Emergency contact notes')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Delivery settings')
                    ->schema([
                        Select::make('route_id')
                            ->label('Delivery route')
                            ->relationship('deliveryRoute', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('diets')
                            ->label('Dietary requirements')
                            ->relationship('diets', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Textarea::make('recurring_order')
                            ->label('Recurring order')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        Textarea::make('route_backup')
                            ->label('Route backup notes')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Status & Notes')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Toggle::make('use_cafeteria')
                            ->label('Uses cafeteria')
                            ->default(false),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
