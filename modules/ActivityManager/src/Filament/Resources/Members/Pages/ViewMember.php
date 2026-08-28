<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\Pages;

use AcMarche\ActivityManager\Filament\Resources\Members\MembersResource;
use AcMarche\ActivityManager\Models\Activity;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\ActivityManager\Models\Schedule;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ViewMember extends ViewRecord
{
    #[Override]
    protected static string $resource = MembersResource::class;

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('attachSchedule')
                ->label('Inscrire à une activité')
                ->icon(Heroicon::AcademicCap)
                ->color('success')
                ->schema([
                    Select::make('activity_id')
                        ->label('Activité')
                        ->options(fn (): array => Activity::query()
                            ->whereHas(
                                'schedules',
                                fn (Builder $query): Builder => $query->whereNotIn(
                                    'id',
                                    $this->record->schedules()->pluck('schedules.id'),
                                ),
                            )
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set): mixed => $set('schedule_id', null)),
                    Select::make('schedule_id')
                        ->label('Cours')
                        ->options(fn (Get $get): array => blank($get('activity_id'))
                            ? []
                            : Schedule::query()
                                ->where('activity_id', $get('activity_id'))
                                ->whereNotIn('id', $this->record->schedules()->pluck('schedules.id'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->schedules()->attach($data['schedule_id']);

                    Notification::make()
                        ->title('Membre inscrit au cours')
                        ->success()
                        ->send();

                    $this->dispatch('member-schedules-updated');
                }),
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash)
                ->modalDescription(fn (?Member $record): string => MembersResource::deleteModalDescription($record)),
        ];
    }
}
