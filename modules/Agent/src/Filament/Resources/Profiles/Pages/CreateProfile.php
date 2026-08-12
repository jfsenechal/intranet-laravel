<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Pages;

use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Repository\LdapRepository;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use LdapRecord\Models\Model;
use Livewire\Attributes\Url;
use Override;

final class CreateProfile extends CreateRecord
{
    #[Url(as: 'employee_id')]
    public ?int $employeeId = null;

    #[Override]
    protected static string $resource = ProfileResource::class;

    protected static ?string $title = 'Ajouter un profil';

    protected static bool $canCreateAnother = false;

    protected ?Employee $employee = null;

    #[Override]
    public function mount(): void
    {
        if ($this->employeeId !== null) {
            $this->employee = Employee::query()
                ->with('activeContracts.service')
                ->find($this->employeeId);

            $existingProfile = $this->existingProfileForEmployee();

            if ($existingProfile instanceof Profile) {
                $this->redirectToExistingProfile($existingProfile);

                return;
            }
        }

        parent::mount();
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        if ($this->employee instanceof Employee) {
            $fullName = mb_trim($this->employee->first_name.' '.$this->employee->last_name);

            return 'Ajouter un profil pour '.$fullName;
        }

        abort(404, 'Employee not found');
    }

    public function form(Schema $schema): Schema
    {
        $components = [];
        if ($this->employee instanceof Employee) {
            $services = $this->employee->activeContracts
                ->map(fn (Contract $contract): ?string => $contract->service?->name)
                ->filter()
                ->unique()
                ->implode(', ');

            $jobFunctions = $this->employee->activeContracts
                ->pluck('job_title')
                ->filter()
                ->unique()
                ->implode(', ');

            $components[] = Section::make('Employé')
                ->columns(2)
                ->schema([
                    TextEntry::make('last_name')
                        ->label('Nom')
                        ->state($this->employee->last_name),
                    TextEntry::make('first_name')
                        ->label('Prénom')
                        ->state($this->employee->first_name),
                    TextEntry::make('job_functions')
                        ->label('Fonctions (contrats actifs)')
                        ->state($jobFunctions !== '' ? $jobFunctions : '—'),
                    TextEntry::make('services')
                        ->label('Services (contrats actifs)')
                        ->state($services !== '' ? $services : '—'),
                    TextEntry::make('hired_at')
                        ->label('Entré le')
                        ->state($this->employee->hired_at?->format('d/m/Y') ?? '—'),
                    TextEntry::make('status')
                        ->label('Statut')
                        ->state($this->employee->status?->getLabel() ?? '—'),
                ]);

            $components[] = Select::make('username')
                ->label('Utilisateur LDAP')
                ->helperText('Lier à la LDAP si celle-ci existe')
                ->options(UserRepository::listLdapUsersForSelect())
                ->searchable();
        }

        return $schema->schema($components);
    }

    /**
     * The employee may have been given a profile by somebody else while this page was open.
     */
    protected function beforeCreate(): void
    {
        $existingProfile = $this->existingProfileForEmployee();

        if ($existingProfile instanceof Profile) {
            $this->redirectToExistingProfile($existingProfile);

            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] ??= (string) Str::uuid();
        $data['emails'] ??= [];
        $data['modules'] ??= [];

        if (! empty($data['username'])
            && ($userLdap = LdapRepository::findByUsername($data['username'])) instanceof Model) {
            $data['first_name'] = $userLdap->getFirstAttribute('givenname') ?? 'prenom pas trouvé';
            $data['last_name'] = $userLdap->getFirstAttribute('sn') ?? 'nom pas trouvé';
        } elseif ($this->employeeId !== null
            && ($employee = Employee::query()->find($this->employeeId)) instanceof Employee) {
            $data['first_name'] = $employee->first_name;
            $data['last_name'] = $employee->last_name;
        }

        if ($this->employeeId !== null) {
            $data['employee_id'] = $this->employeeId;
        }

        return $data;
    }

    private function existingProfileForEmployee(): ?Profile
    {
        if ($this->employeeId === null) {
            return null;
        }

        return Profile::query()
            ->where('employee_id', $this->employeeId)
            ->first();
    }

    private function redirectToExistingProfile(Profile $profile): void
    {
        Notification::make()
            ->title('Un profil existe déjà pour cet agent')
            ->body('Vous avez été redirigé vers le profil existant.')
            ->warning()
            ->send();

        $this->redirect(ViewProfile::getUrl(['record' => $profile->getKey()], panel: 'agent-panel'));
    }
}
