<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Pages;

use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Repository\LdapRepository;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
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
        parent::mount();

        if ($this->employeeId !== null) {
            $this->employee = Employee::query()
                ->with(['activeContracts.service', 'savedEmployer'])
                ->find($this->employeeId);
        }
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        if ($this->employee instanceof Employee) {
            $fullName = mb_trim($this->employee->first_name.' '.$this->employee->last_name);

            return 'Ajouter un profil pour '.$fullName;
        }

        return self::$title ?? 'Ajouter un profil';
    }

    public function form(Schema $schema): Schema
    {
        $components = [];

        if ($this->employee instanceof Employee) {
            $services = $this->employee->activeContracts
                ->map(fn ($contract) => $contract->service?->name)
                ->filter()
                ->unique()
                ->implode(', ');

            $components[] = Section::make('Employé')
                ->columns(2)
                ->schema([
                    Placeholder::make('last_name')
                        ->label('Nom')
                        ->content($this->employee->last_name),
                    Placeholder::make('first_name')
                        ->label('Prénom')
                        ->content($this->employee->first_name),
                    Placeholder::make('services')
                        ->label('Services (contrats actifs)')
                        ->content($services !== '' ? $services : '—'),
                    Placeholder::make('hired_at')
                        ->label('Entré le')
                        ->content($this->employee->hired_at?->format('d/m/Y') ?? '—'),
                    Placeholder::make('status')
                        ->label('Statut')
                        ->content((string) $this->employee->status),
                ]);
        }

        $components[] = Select::make('username')
            ->label('Utilisateur LDAP')
            ->options(UserRepository::listLdapUsersForSelect())
            ->searchable()
            ->required();

        return $schema->schema($components);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] ??= (string) Str::uuid();
        $data['emails'] ??= [];
        $data['modules'] ??= [];

        if (! empty($data['username'])) {
            if (($userLdap = LdapRepository::findByUsername($data['username'])) instanceof Model) {
                $data['first_name'] = $userLdap->getFirstAttribute('givenname');
                $data['last_name'] = $userLdap->getFirstAttribute('sn');
            }
        }

        if ($this->employeeId !== null) {
            $data['employee_id'] = $this->employeeId;
        }

        return $data;
    }
}
