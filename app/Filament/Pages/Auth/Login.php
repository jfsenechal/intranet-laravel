<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BasePage;

class Login extends BasePage
{
    public function mount(): void
    {
        parent::mount();
        if (!app()->environment('production')) {
            $this->form->fill([
                'email' => 'jf@marche.be',
                'password' => 'marge',
                'remember' => true,
            ]);
        }
    }
}
