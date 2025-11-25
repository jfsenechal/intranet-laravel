<?php

namespace App\Filament\Admin\Pages;

use Filament\Auth\Pages\Login as BasePage;

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
