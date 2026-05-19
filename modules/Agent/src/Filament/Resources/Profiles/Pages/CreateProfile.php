<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Pages;

use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Repository\LdapRepository;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
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
                ->find($this->employeeId);
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
                ->map(fn ($contract) => $contract->service?->name)
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
}
