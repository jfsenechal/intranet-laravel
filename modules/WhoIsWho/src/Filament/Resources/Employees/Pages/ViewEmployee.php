<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Resources\Employees\Pages;

use AcMarche\Hrm\Models\Employee;
use AcMarche\WhoIsWho\Filament\Pages\Index;
use AcMarche\WhoIsWho\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewEmployee extends ViewRecord
{
    #[Override]
    protected static string $resource = EmployeeResource::class;

    public function getTitle(): string
    {
        $employee = $this->employee();

        return mb_trim($employee->last_name.' '.$employee->first_name);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToDirectory')
                ->label('Retour à l\'annuaire')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->url(fn (): string => Index::getUrl([
                    'letter' => mb_strtoupper(mb_substr($this->employee()->last_name, 0, 1)),
                ])),
        ];
    }

    private function employee(): Employee
    {
        /** @var Employee $employee */
        $employee = $this->getRecord();

        return $employee;
    }
}
