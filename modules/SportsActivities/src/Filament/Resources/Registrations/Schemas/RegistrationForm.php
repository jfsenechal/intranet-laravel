<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations\Schemas;

use AcMarche\SportsActivities\Models\Activity;
use AcMarche\SportsActivities\Models\Group;
use AcMarche\SportsActivities\Models\Member;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

final class RegistrationForm
{
    /**
     * Schema used when the group is already known and the member must be chosen.
     *
     * @return array<int, mixed>
     */
    public static function schemaSelectMember(): array
    {
        return [
            self::memberSelect(),
            ...self::baseSchema(fn(Group $record): ?float => $record->price),
        ];
    }

    /**
     * Schema used when the member is already known and the activity/group must be chosen.
     *
     * @return array<int, mixed>
     */
    public static function schemaSelectActivity(): array
    {
        return [
            self::activitySelect(),
            self::groupSelect(),
            ...self::baseSchema(),
        ];
    }

    /**
     * @param Closure|null $priceDefault Resolves the default price (e.g. from the group).
     * @return array<int, mixed>
     */
    public static function baseSchema(?Closure $priceDefault = null): array
    {
        return [
            Section::make('Inscription')
                ->schema([
                    TextInput::make('price')
                        ->label('Prix')
                        ->helperText('Vous pouvez changer le prix si vous accordez une réduction')
                        ->numeric()
                        ->default($priceDefault),
                    Textarea::make('comment')
                        ->label('Remarque')
                        ->rows(3),
                ]),
        ];
    }

    private static function memberSelect(): Select
    {
        return Select::make('member_id')
            ->label('Sportif')
            ->searchable()
            ->required()
            ->getSearchResultsUsing(fn(string $search): array => Member::query()
                ->where('last_name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orderBy('last_name')
                ->limit(50)
                ->get()
                ->mapWithKeys(fn(Member $member): array => [
                    $member->id => "{$member->last_name} {$member->first_name}",
                ])
                ->toArray())
            ->getOptionLabelUsing(function ($value): ?string {
                $member = Member::find($value);

                return $member instanceof Member ? "{$member->last_name} {$member->first_name}" : null;
            });
    }

    private static function activitySelect(): Select
    {
        return Select::make('activity_id')
            ->label('Activité')
            ->options(fn(): array => Activity::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray())
            ->searchable()
            ->required()
            ->live()
            ->afterStateUpdated(fn(Set $set) => $set('group_id', null));
    }

    private static function groupSelect(): Select
    {
        return Select::make('group_id')
            ->label('Groupe')
            ->required()
            ->options(function (Get $get): array {
                $activityId = $get('activity_id');
                if (!$activityId) {
                    return [];
                }

                return Group::query()
                    ->where('activity_id', $activityId)
                    ->get()
                    ->mapWithKeys(fn(Group $group): array => [
                        $group->id => "{$group->day} — {$group->time} — {$group->location}",
                    ])
                    ->toArray();
            })
            ->searchable()
            ->live()
            ->afterStateUpdated(function (Set $set, mixed $state): void {
                $group = $state ? Group::find($state) : null;
                $set('price', $group?->price);
            });
    }
}
