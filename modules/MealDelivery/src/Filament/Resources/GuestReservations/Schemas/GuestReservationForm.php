<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Schemas;

use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Resident;
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
        return $schema->schema([self::section(null)]);
    }

    /**
     * The same form, nested under a resident who is already known: the relation
     * manager hangs the reservation off the owner record itself, so the select
     * would be noise — and the uniqueness rule has no field left to read the
     * resident from, hence the explicit id.
     */
    public static function configureForResident(Schema $schema, Resident $resident): Schema
    {
        return $schema->schema([self::section($resident)]);
    }

    /**
     * A resident books at most one guest reservation per day, so the two menu
     * counts always live on the same row. The rule is written by hand rather than
     * with Laravel's `unique`, which would query the default connection instead of
     * `maria-meal-delivery`.
     */
    public static function uniquePerResidentAndDate(?int $residentId = null): Closure
    {
        return static fn (Get $get, ?Model $record): Closure => static function (
            string $attribute,
            mixed $value,
            Closure $fail,
        ) use ($get, $record, $residentId): void {
            $resident = $residentId ?? $get('resident_id');

            if (blank($resident) || blank($value)) {
                return;
            }

            $exists = GuestReservation::query()
                ->where('resident_id', $resident)
                ->whereDate('date', CarbonImmutable::parse((string) $value)->format('Y-m-d'))
                ->when(
                    $record instanceof GuestReservation,
                    fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()),
                )
                ->exists();

            if ($exists) {
                $fail('Une réservation existe déjà pour ce résident à cette date.');
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

    private static function section(?Resident $resident): Section
    {
        return Section::make('Réservation')
            ->schema([
                Grid::make(2)
                    ->schema(array_values(array_filter([
                        $resident instanceof Resident ? null : self::residentSelect(),
                        self::datePicker($resident?->id),
                    ]))),

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
            ]);
    }

    private static function residentSelect(): Select
    {
        return Select::make('resident_id')
            ->label('Résident')
            ->helperText('Le résident qui reçoit de la famille.')
            ->relationship(
                'resident',
                'last_name',
                fn (Builder $query): Builder => $query
                    ->where('is_active', true)
                    ->orderBy('last_name')
                    ->orderBy('first_name'),
            )
            ->getOptionLabelFromRecordUsing(fn (Model $record): string => $record->fullName())
            ->searchable(['last_name', 'first_name'])
            ->preload()
            ->required();
    }

    private static function datePicker(?int $residentId): DatePicker
    {
        return DatePicker::make('date')
            ->label('Date du repas')
            ->helperText('Repas de midi.')
            ->required()
            ->rule(self::uniquePerResidentAndDate($residentId));
    }
}
