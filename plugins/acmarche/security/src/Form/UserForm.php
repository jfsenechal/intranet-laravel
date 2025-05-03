<?php


namespace AcMarche\Security\Form;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

class UserForm
{
    public static function createForm(Form $form): Form
    {
        return $form
            ->schema([]);
    }

}
