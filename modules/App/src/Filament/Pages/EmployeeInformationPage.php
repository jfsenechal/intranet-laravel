<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\Hrm\Filament\Resources\Employees\Schemas\EmployeeInfolist;
use AcMarche\Hrm\Models\Employee;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Override;

final class EmployeeInformationPage extends Page
{
    public ?Employee $employee = null;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-identification';

    #[Override]
    protected static ?string $navigationLabel = 'Ma fiche RH';

    #[Override]
    protected string $view = 'app::filament.pages.employee-information';

    public function getTitle(): string
    {
        return 'Ma fiche RH';
    }

    public function mount(): void
    {
        $username = Auth::user()?->username;
        abort_unless($username !== null, 403);

        $this->employee = Employee::query()
            ->where('username', $username)
            ->first();
    }

    public function employeeInfolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema, includeComputerAccountTab: false)
            ->record($this->employee);
    }
}
