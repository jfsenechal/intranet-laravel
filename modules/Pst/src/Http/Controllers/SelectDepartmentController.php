<?php

declare(strict_types=1);

namespace AcMarche\Pst\Http\Controllers;

use AcMarche\Security\Repository\UserRepository;
use Illuminate\Http\RedirectResponse;

final class SelectDepartmentController extends Controller
{
    public function select(string $department): RedirectResponse
    {
        session()->put(UserRepository::$department_selected_key, $department);

        return redirect('/admin');
    }
}
