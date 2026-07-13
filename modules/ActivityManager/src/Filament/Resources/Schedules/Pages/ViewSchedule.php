<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Pages;

use AcMarche\ActivityManager\Filament\Resources\Schedules\SchedulesResource;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Schemas\ScheduleInfolist;
use AcMarche\ActivityManager\Models\Member;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewSchedule extends ViewRecord
{
    #[Override]
    protected static string $resource = SchedulesResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return ScheduleInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('attachMember')
                ->label('Inscrire un membre')
                ->icon(Heroicon::UserPlus)
                ->schema([
                    Select::make('member_id')
                        ->label('Membre')
                        ->options(fn (): array => Member::query()
                            ->whereNotIn('id', $this->record->members()->pluck('members.id'))
                            ->orderBy('last_name')
                            ->orderBy('first_name')
                            ->get()
                            ->mapWithKeys(fn (Member $member): array => [
                                $member->id => "{$member->last_name} {$member->first_name}",
                            ])
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->members()->attach($data['member_id']);
                }),
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
