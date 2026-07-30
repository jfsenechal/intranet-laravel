<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Resources\Employees\Schemas;

use AcMarche\Hrm\Models\Employee;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * Read-only staff directory card.
 *
 * Only professional information is exposed here: the directory is visible to
 * every authenticated user, so private contact details, address, national
 * registry number and HR data must never appear.
 */
final class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make()
                                    ->schema([
                                        ImageEntry::make('photo')
                                            ->label('Photo')
                                            ->hiddenLabel()
                                            ->circular()
                                            ->imageSize(160)
                                            ->state(fn (Employee $record): string => EmployeeRepository::photoUrl($record)),

                                        TextEntry::make('last_name')
                                            ->label('Nom')
                                            ->weight('bold')
                                            ->state(fn (Employee $record): string => mb_trim($record->last_name.' '.$record->first_name)),

                                        TextEntry::make('birth_date')
                                            ->label('Anniversaire')
                                            ->icon('heroicon-o-cake')
                                            ->visible(fn (Employee $record): bool => $record->show_birthday === true && $record->birth_date !== null)
                                            ->state(fn (Employee $record): ?string => $record->birth_date?->translatedFormat('d F')),

                                        Actions::make([
                                            Action::make('editPhoto')
                                                ->label('Changer ma photo')
                                                ->icon(Heroicon::OutlinedCamera)
                                                ->link()
                                                ->url(fn (): ?string => Filament::getPanel('app-panel')->getProfileUrl())
                                                ->visible(fn (Employee $record): bool => self::isOwnRecord($record)),
                                        ])->alignment(Alignment::Center),
                                    ]),
                            ]),

                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Fonctions')
                                    ->schema([
                                        RepeatableEntry::make('activeContracts')
                                            ->hiddenLabel()
                                            ->columns(3)
                                            ->contained(false)
                                            ->schema([
                                                TextEntry::make('job_title')
                                                    ->label('Fonction')
                                                    ->placeholder('—'),

                                                TextEntry::make('service.name')
                                                    ->label('Service')
                                                    ->placeholder('—'),

                                                TextEntry::make('direction.name')
                                                    ->label('Direction')
                                                    ->placeholder('—'),
                                            ]),
                                    ]),

                                Section::make('Coordonnées professionnelles')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('professional_email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable()
                                            ->placeholder('—')
                                            ->url(fn (Employee $record): ?string => $record->professional_email !== null
                                                ? 'mailto:'.$record->professional_email
                                                : null),

                                        TextEntry::make('professional_phone')
                                            ->label('Téléphone')
                                            ->icon('heroicon-o-phone')
                                            ->copyable()
                                            ->placeholder('—')
                                            ->state(fn (Employee $record): ?string => self::phoneWithExtension($record))
                                            ->url(fn (Employee $record): ?string => $record->professional_phone !== null
                                                ? 'tel:'.$record->professional_phone
                                                : null),

                                        TextEntry::make('professional_mobile')
                                            ->label('GSM')
                                            ->icon('heroicon-o-device-phone-mobile')
                                            ->copyable()
                                            ->placeholder('—')
                                            ->url(fn (Employee $record): ?string => $record->professional_mobile !== null
                                                ? 'tel:'.$record->professional_mobile
                                                : null),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * Format the desk phone as `084 12 34 56 (ext. 123)` when an extension is set.
     */
    public static function phoneWithExtension(Employee $employee): ?string
    {
        if ($employee->professional_phone === null) {
            return null;
        }

        if (blank($employee->professional_phone_extension)) {
            return $employee->professional_phone;
        }

        return $employee->professional_phone.' (ext. '.$employee->professional_phone_extension.')';
    }

    /**
     * The directory photo comes from the User avatar matched by `username`
     * (see `EmployeeRepository::photoUrl()`), so only the agent looking at
     * their own entry is offered the link to the profile page.
     */
    private static function isOwnRecord(Employee $employee): bool
    {
        $user = Auth::user();

        if (! $user instanceof User || blank($user->username)) {
            return false;
        }

        return $user->username === $employee->username;
    }
}
