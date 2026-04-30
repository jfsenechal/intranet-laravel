<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\Pages;

use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Models\Employee;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Override;

final class CreateEmployee extends CreateRecord
{
    #[Override]
    protected static string $resource = EmployeeResource::class;

    protected static ?string $title = 'Ajouter un agent';

    protected static bool $canCreateAnother = false;

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identite')
                    ->columns(2)
                    ->schema([
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100)
                            ->live(debounce: 400),
                        TextInput::make('first_name')
                            ->label('Prenom')
                            ->required()
                            ->maxLength(100)
                            ->live(debounce: 400),
                    ]),
                View::make('hrm::filament.employees.create-matches')
                    ->viewData(function (Get $get): array {
                        $lastName = mb_trim((string) $get('last_name'));
                        $firstName = mb_trim((string) $get('first_name'));

                        $matches = collect();
                        if (mb_strlen($lastName) >= 2) {
                            $matches = Employee::query()
                                ->where('last_name', 'like', '%'.$lastName.'%')
                                ->when(
                                    $firstName !== '',
                                    fn ($query) => $query->where('first_name', 'like', '%'.$firstName.'%'),
                                )
                                ->orderBy('last_name')
                                ->orderBy('first_name')
                                ->limit(20)
                                ->get();
                        }

                        return [
                            'lastName' => $lastName,
                            'firstName' => $firstName,
                            'matches' => $matches,
                        ];
                    }),
            ]);
    }

    #[Override]
    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
