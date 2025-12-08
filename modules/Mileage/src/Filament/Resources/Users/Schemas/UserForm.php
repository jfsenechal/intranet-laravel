<?php

namespace AcMarche\Mileage\Filament\Resources\Users\Schemas;

use AcMarche\Security\Filament\Resources\ModuleResource\Schema\ModuleForm;
use AcMarche\Security\Repository\ModuleRepository;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $mileageModule = ModuleRepository::find(13);

        return $schema
            ->components([
                Select::make('username')
                    ->label('Agent')
                    ->options(fn(UserRepository $repository): array => $repository->getUsersForSelect())
                    ->searchable()
                    ->columnSpanFull(),
                DatePicker::make('college_trip_date')
                    ->label('Date de la décision du Collège')
                    ->required(),
                Checkbox::make('omnium')
                    ->label('Retenue omnium')
                    ->helperText('Cochez pour oui'),
                ModuleForm::rolesField($mileageModule),
            ]);
    }

}
