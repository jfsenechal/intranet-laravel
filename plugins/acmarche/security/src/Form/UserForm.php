<?php


namespace AcMarche\Security\Form;

use App\Ldap\User;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

class UserForm
{
    public static function createForm(Form $form, Model|User $record = null): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('second_name')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->label('Email address')
                    ->maxLength(120),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }

}
