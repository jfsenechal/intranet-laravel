<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Schemas;

use AcMarche\GuichetHdv\Enums\ServicesEnum;
use AcMarche\GuichetHdv\Models\Reason;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

final class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make([
                    Grid::make(2)->schema([
                        TextInput::make('number')
                            ->label('Numéro')
                            ->required()
                            ->autocomplete(false)
                            ->maxLength(255)
                            ->unique(
                                table: 'maria-guichet.tickets',
                                column: 'number',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, ?Model $record): Unique => $rule->where(
                                    fn ($query) => $query->whereDate(
                                        'createdAt',
                                        ($record?->createdAt ?? now())->toDateString(),
                                    ),
                                ),
                            )
                            ->validationMessages([
                                'unique' => 'Un ticket portant ce numéro existe déjà pour aujourd\'hui.',
                            ]),
                        Select::make('service')
                            ->label('Service')
                            ->options(ServicesEnum::class)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('suggest_reason', null)),
                        Textarea::make('reason')
                            ->label('Motif')
                            ->required(),
                        Select::make('suggest_reason')
                            ->label('Suggestion de motif')
                            ->options(fn (Get $get): array => self::reasonOptions($get('service')))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('reason', $state))
                            ->dehydrated(false)
                            ->columnSpan(1),
                    ]),
                ])->heading('Informations'),

                Grid::make(2)->schema([
                    Toggle::make('archive')
                        ->label('Archiver')
                        ->default(false),
                ]),
            ]);
    }

    /**
     * Motifs attached to the given service, plus those available to every service.
     *
     * @return array<string, string>
     */
    private static function reasonOptions(ServicesEnum|string|null $service): array
    {
        return Reason::query()
            ->when(
                $service,
                fn (Builder $query, ServicesEnum|string $service): Builder => $query->where(
                    fn (Builder $query): Builder => $query
                        ->where('service', $service instanceof ServicesEnum ? $service->value : $service)
                        ->orWhereNull('service'),
                ),
            )
            ->orderBy('content')
            ->pluck('content', 'content')
            ->all();
    }
}
