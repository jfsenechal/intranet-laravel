<?php

namespace AcMarche\Security\Form;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\RoleRepository;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

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
                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->pivotData(fn(Module $record) => [
                        'module_id' => $record->id,
                    ])
                    ->visible(fn(Module $module) => $module->id > 0)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->default('ROLE_MODULE_NAME_X'),
                        Forms\Components\TextInput::make('description')
                            ->required()
                    ])
                    ->createOptionUsing(function (Module $record, array $data) {
                        if (RoleRepository::findByNameAndModuleId($data['name'], $record->id)) {
                            Notification::make()
                                ->title('Ce nom existe déjà')
                                ->danger()
                                ->send();

                            return;
                        }

                        Role::create(
                            [
                                'name' => $data['name'],
                                'description' => $data['description'],
                                'module_id' => $record->id ?? null,
                            ]
                        );
                        Notification::make()
                            ->title('Rôle créé')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
