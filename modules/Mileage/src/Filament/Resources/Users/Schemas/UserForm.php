<?php

namespace AcMarche\Mileage\Filament\Resources\Users\Schemas;

use AcMarche\Security\Filament\Resources\Modules\Schemas\ModuleForm;
use AcMarche\Security\Repository\ModuleRepository;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $mileageModule = ModuleRepository::find(13);

        return $schema
            ->components([
                Select::make('username')
                    ->label('Agent')
                    ->options(fn (UserRepository $repository): array => $repository->getUsersForSelect())
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
                    ->hiddenOn(Operation::Edit),
                DatePicker::make('college_trip_date')
                    ->label('Date de la décision du Collège')
                    ->required(false),
                Checkbox::make('omnium')
                    ->label('Retenue omnium')
                    ->helperText('Cochez pour oui'),
                ModuleForm::rolesField($mileageModule),
            ]);
    }
}
