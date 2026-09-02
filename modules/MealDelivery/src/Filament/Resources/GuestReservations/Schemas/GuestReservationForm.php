<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Schemas;

use AcMarche\MealDelivery\Models\GuestReservation;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class GuestReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Réservation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('client_id')
                                    ->label('Client')
                                    ->helperText('Seuls les clients qui mangent à la cafétéria sont proposés.')
                                    ->relationship(
                                        'client',
                                        'last_name',
                                        fn (Builder $query): Builder => $query
                                            ->where('use_cafeteria', true)
                                            ->where('is_active', true)
                                            ->orderBy('last_name')
                                            ->orderBy('first_name'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Model $record): string => mb_trim(
                                            $record->last_name.' '.$record->first_name,
                                        ),
                                    )
                                    ->searchable(['last_name', 'first_name'])
                                    ->preload()
                                    ->required(),

                                DatePicker::make('date')
                                    ->label('Date du repas')
                                    ->helperText('Repas de midi.')
                                    ->required()
                                    ->rule(self::uniquePerClientAndDate()),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('menu1_count')
                                    ->label('Menu 1')
                                    ->helperText('Nombre de repas invités au menu 1.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->rule(self::atLeastOneMeal()),

                                TextInput::make('menu2_count')
                                    ->label('Menu 2')
                                    ->helperText('Nombre de repas invités au menu 2.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                            ]),

                        Textarea::make('notes')
                            ->label('Remarques')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * A client books at most one guest reservation per day, so the two menu
     * counts always live on the same row. Reused by the shortcut action on the
     * order page, which feeds `client_id` through a hidden field.
     */
    public static function uniquePerClientAndDate(): Closure
    {
        return static fn (Get $get, ?Model $record): Closure => static function (
            string $attribute,
            mixed $value,
            Closure $fail,
        ) use ($get, $record): void {
            $clientId = $get('client_id');

            if (blank($clientId) || blank($value)) {
                return;
            }

            $exists = GuestReservation::query()
                ->where('client_id', $clientId)
                ->whereDate('date', CarbonImmutable::parse((string) $value)->format('Y-m-d'))
                ->when(
                    $record instanceof GuestReservation,
                    fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()),
                )
                ->exists();

            if ($exists) {
                $fail('Une réservation existe déjà pour ce client à cette date.');
            }
        };
    }

    public static function atLeastOneMeal(): Closure
    {
        return static fn (Get $get): Closure => static function (
            string $attribute,
            mixed $value,
            Closure $fail,
        ) use ($get): void {
            if ((int) $value + (int) $get('menu2_count') < 1) {
                $fail('Encodez au moins un repas invité.');
            }
        };
    }
}
