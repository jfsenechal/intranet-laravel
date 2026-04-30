<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\Pages;

use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use AcMarche\Hrm\Filament\Resources\Applications\Schemas\ApplicationForm;
use AcMarche\Hrm\Filament\Resources\Contracts\ContractResource;
use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use AcMarche\Hrm\Filament\Resources\Diplomas\DiplomaResource;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Filament\Resources\Evaluations\Schemas\EvaluationForm;
use AcMarche\Hrm\Filament\Resources\HrDocuments\Schemas\HrDocumentForm;
use AcMarche\Hrm\Filament\Resources\Internships\Schemas\InternshipForm;
use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use AcMarche\Hrm\Filament\Resources\Trainings\TrainingResource;
use AcMarche\Hrm\Filament\Resources\Valorizations\Schemas\ValorizationForm;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Override;

final class ViewEmployee extends ViewRecord
{
    #[Override]
    protected static string $resource = EmployeeResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    protected function getHeaderActions(): array
    {
        $employeeId = ['employee_id' => $this->record->id];

        return [
            EditAction::make()
                ->icon(Heroicon::Pencil),
            ActionGroup::make([
                ActionGroup::make([
                    Action::make('addAbsence')
                        ->label('Ajouter une absence')
                        ->icon('tabler-plus')
                        ->url(AbsenceResource::getUrl('create', $employeeId)),
                    Action::make('addDeadline')
                        ->label('Ajouter une échéance')
                        ->icon('tabler-plus')
                        ->url(DeadlineResource::getUrl('create', $employeeId)),
                    Action::make('addContract')
                        ->label('Ajouter un contrat')
                        ->icon('tabler-plus')
                        ->url(ContractResource::getUrl('create', $employeeId)),
                    Action::make('addTraining')
                        ->label('Ajouter une formation')
                        ->icon('tabler-plus')
                        ->url(TrainingResource::getUrl('create', $employeeId)),
                    Action::make('addSms')
                        ->label('Ajouter un rappel SMS')
                        ->icon('tabler-plus')
                        ->url(SmsReminderResource::getUrl('create', $employeeId)),
                    Action::make('addDocument')
                        ->label('Ajouter un document')
                        ->icon('tabler-plus')
                        ->modal()
                        ->modalHeading('Ajouter un document')
                        ->schema(HrDocumentForm::getSchema())
                        ->action(function (array $data, Employee $record): void {
                            $path = $data['file_name'] ?? null;
                            $record->documents()->create([
                                'name' => $data['name'],
                                'file_name' => $path,
                                'mime' => $path ? (Storage::disk('public')->mimeType($path) ?: '') : '',
                                'notes' => $data['notes'] ?? null,
                            ]);
                        })
                        ->successNotificationTitle('Document ajouté'),
                    Action::make('addDiploma')
                        ->label('Ajouter un diplôme')
                        ->icon('tabler-plus')
                        ->url(DiplomaResource::getUrl('create', $employeeId)),
                    CreateAction::make('addEvaluation')
                        ->label('Ajouter une évaluation')
                        ->icon('tabler-plus')->modal()
                        ->schema(fn (Schema $schema) => EvaluationForm::configure($schema))
                        ->action(function (array $data, Employee $record): void {
                            $record->evaluations()->create($data);
                        }),

                ])->dropdown(false),
                CreateAction::make('addValorization')
                    ->label('Ajouter une valorisation')
                    ->icon('tabler-plus')
                    ->modal()
                    ->schema(fn (Schema $schema) => ValorizationForm::configure($schema))
                    ->modalHeading(function (Employee $employee): string {
                        return 'Ajouter une valorisation pour '.$employee->last_name.' '.$employee->first_name;
                    })
                    ->action(function (array $data, Employee $record): void {
                        $record->valorizations()->create($data);
                    }),
                CreateAction::make('addInternship')
                    ->label('Ajouter un stage')
                    ->icon('tabler-plus')
                    ->modal()
                    ->schema(fn (Schema $schema) => InternshipForm::configure($schema))
                    ->modalHeading(function (Employee $employee): string {
                        return 'Ajouter un stage pour '.$employee->last_name.' '.$employee->first_name;
                    })
                    ->action(function (array $data, Employee $record): void {
                        $record->internships()->create($data);
                    }),
                CreateAction::make('addApplication')
                    ->label('Ajouter une candidature')
                    ->icon('tabler-plus')
                    ->modal()
                    ->schema(fn (Schema $schema) => ApplicationForm::configure($schema))
                    ->mountUsing(function (Schema $schema, Employee $employee): void {
                        $schema->fill($employee ? ['employee_id' => $employee->id] : []);
                    })
                    ->modalHeading(function (Employee $employee): string {
                        return 'Ajouter une candidature pour '.$employee->last_name.' '.$employee->first_name;
                    })
                    ->action(function (array $data, Employee $record): void {
                        $record->applications()->create($data);
                    }),
            ])
                ->label('Ajouter...')
                ->color('warning')
                ->icon('tabler-plus')
                ->dropdownWidth(Width::FitContent)
                ->button(),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
