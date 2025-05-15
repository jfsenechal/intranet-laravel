<?php

namespace AcMarche\Security\Form;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

class ModuleForm
{
    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('url')
                    ->label('Url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Checkbox::make('is_public')
                    ->label('Publique')
                    ->helperText('Accessible à tous'),
                Forms\Components\Checkbox::make('is_external')
                    ->label('Externe')
                    ->helperText('Url externe'),
                Forms\Components\ColorPicker::make('color')
                    ->label('Couleur')
                    ->required(),
                Forms\Components\TextInput::make('icon')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function userForm(Form $form, Model|Module $owner): Form
    {
        $user = $form->getRecord();
        $roles = RoleRepository::getForSelect($owner);
        $rolesName = $roles[0];
        $rolesDescription = $roles[1];
        $schema = [];

        if (!$user) {
            $schema[] = Forms\Components\Select::make('user')
                ->label('Utilisateur')
                ->options(fn(UserRepository $repository): array => $repository->getUsersForSelect())
                ->columnSpanFull();
        }

        $schema[] = Forms\Components\CheckboxList::make('roles')
            ->label('Rôles')
            ->options($rolesName)
            ->descriptions($rolesDescription);

        return $form->schema($schema);
    }
}
