<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Pages;

use AcMarche\Hrm\Models\Service;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;
use Filament\Pages\Page;
use Override;

final class Services extends Page
{
    public ?int $serviceId = null;

    #[Override]
    protected string $view = 'who-is-who::filament.pages.services';

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Services';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-office-2';
    }

    public function getTitle(): string
    {
        return 'Annuaire par service';
    }

    public function mount(): void
    {
        $serviceId = request()->query('service');
        $this->serviceId = $serviceId !== null ? (int) $serviceId : null;
    }

    public function selectService(?int $serviceId): void
    {
        $this->serviceId = $serviceId;
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        $services = EmployeeRepository::servicesWithAgents();

        $selectedService = $this->serviceId !== null
            ? $services->firstWhere('id', $this->serviceId)
            : null;

        $employees = $selectedService instanceof Service
            ? EmployeeRepository::agentsByService($selectedService->id)
            : null;

        return [
            'services' => $services,
            'selectedService' => $selectedService,
            'employees' => $employees,
        ];
    }
}
