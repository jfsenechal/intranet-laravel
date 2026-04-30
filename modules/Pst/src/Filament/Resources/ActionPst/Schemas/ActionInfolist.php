<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Schemas;

use AcMarche\Pst\Enums\ActionRoadmapEnum;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\ActionStateEnum;
use AcMarche\Pst\Enums\ActionSynergyEnum;
use AcMarche\Pst\Enums\ActionTypeEnum;
use AcMarche\Pst\Filament\Components\ProgressEntry;
use AcMarche\Pst\Models\Odd;
use AcMarche\Pst\Models\Partner;
use AcMarche\Pst\Models\Service;
use App\Models\User;
use DateTimeImmutable;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\TextSize;

final class ActionInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make([
                    Section::make('Informations')
                        ->label(null)
                        ->schema(self::informations())
                        ->grow(),
                    Section::make('Statut')
                        ->label(null)
                        ->schema(self::statut())
                        ->grow(false),
                ])->from('md')
                    ->columnSpanFull(),
            ]);
    }

    private static function odd(): TextEntry
    {
        return TextEntry::make('odds')
            ->label('Objectifs de développement durable')
            ->formatStateUsing(
                fn (Odd $state): string => $state->name
            )
            ->size(TextSize::Large)
            ->color(Color::Pink)
            ->badge();
    }

    private static function budget(): Fieldset
    {
        return Fieldset::make('budget')
            ->label('Financement')
            ->columns(2)
            ->columnSpanFull()
            ->schema([
                TextEntry::make('budget_estimate')
                    ->markdown()
                    ->label('Budget estimé')
                    ->prose(),
                TextEntry::make('financing_mode')
                    ->markdown()
                    ->label('Mode de financement')
                    ->prose(),
            ]);
    }

    private static function informations(): array
    {
        return [
            TextEntry::make('description')
                ->label('Description')
                ->html()
                ->prose()
                ->columnSpanFull()
                ->visible(fn (?string $state): bool => $state !== null && $state !== ''),

            TextEntry::make('note')
                ->label('Notes')
                ->html()
                ->prose()
                ->columnSpanFull()
                ->visible(fn (?string $state): bool => $state !== null && $state !== ''),

            Fieldset::make('team')
                ->label('Équipe')
                ->columns(['default' => 1, 'md' => 2, 'lg' => 3])
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('users')
                        ->label('Agents pilotes')
                        ->badge()
                        ->color('success')
                        ->formatStateUsing(
                            fn (User $state): string => $state->last_name.' '.$state->first_name
                        ),
                    TextEntry::make('mandataries')
                        ->label('Mandataires')
                        ->badge()
                        ->color('warning')
                        ->formatStateUsing(
                            fn (User $state): string => $state->last_name.' '.$state->first_name
                        ),
                    TextEntry::make('leaderServices')
                        ->label('Services porteurs')
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(fn (Service $state): string => $state->name),
                    TextEntry::make('partnerServices')
                        ->label('Services partenaires')
                        ->badge()
                        ->color('info')
                        ->formatStateUsing(fn (Service $state): string => $state->name),
                    TextEntry::make('partners')
                        ->label('Partenaires externes')
                        ->badge()
                        ->color('gray')
                        ->formatStateUsing(fn (Partner $state): string => $state->name),
                ]),

            self::odd(),

            self::budget(),

            Grid::make(['default' => 1, 'lg' => 2])
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('work_plan')
                        ->label('Plan de travail')
                        ->html()
                        ->prose(),
                    TextEntry::make('evaluation_indicator')
                        ->label('Indicateur d\'évaluation')
                        ->html()
                        ->prose(),
                ]),
        ];
    }

    private static function statut(): array
    {
        return [
            Section::make('Classification')
                ->compact()
                ->schema([
                    TextEntry::make('type')
                        ->label('Type')
                        ->formatStateUsing(fn (ActionTypeEnum $state): string => $state->getLabel())
                        ->icon(fn (ActionTypeEnum $state): ?string => $state->getIcon())
                        ->color(fn (ActionTypeEnum $state): string|array|null => $state->getColor())
                        ->badge(),
                    IconEntry::make('scope')
                        ->label('Interne')
                        ->formatStateUsing(fn (?ActionScopeEnum $state): string => $state?->getLabel() ?? '-')
                        ->boolean()
                        ->size(IconSize::Medium),
                    TextEntry::make('synergy')
                        ->label(ActionSynergyEnum::getTitle())
                        ->helperText(ActionSynergyEnum::getDescription())
                        ->formatStateUsing(fn (?ActionSynergyEnum $state): string => $state?->getLabel() ?? '-'),
                    TextEntry::make('roadmap')
                        ->label('Feuille de route')
                        ->formatStateUsing(fn (?ActionRoadmapEnum $state): string => $state?->getLabel() ?? '-')
                        ->badge()
                        ->color('gray'),
                ]),

            Section::make('Avancement')
                ->compact()
                ->schema([
                    TextEntry::make('state')
                        ->label('État')
                        ->formatStateUsing(fn (ActionStateEnum $state): string => $state->getLabel())
                        ->icon(fn (ActionStateEnum $state): string => $state->getIcon())
                        ->color(fn (ActionStateEnum $state): string|array|null => $state->getColor())
                        ->badge(),
                    ProgressEntry::make('state_percentage')
                        ->label('Progression'),
                    TextEntry::make('due_date')
                        ->label('Échéance')
                        ->visible(fn (?DateTimeImmutable $date): bool => $date instanceof DateTimeImmutable)
                        ->date()
                        ->icon('heroicon-o-calendar')
                        ->color('danger'),
                ]),

            Section::make('Métadonnées')
                ->compact()
                ->collapsed()
                ->schema([
                    TextEntry::make('department')
                        ->label('Département'),
                    TextEntry::make('created_at')
                        ->label('Créé le')
                        ->dateTime()
                        ->icon('heroicon-o-clock'),
                    TextEntry::make('user_add')
                        ->label('Créé par')
                        ->icon('heroicon-o-user'),
                ]),
        ];
    }
}
