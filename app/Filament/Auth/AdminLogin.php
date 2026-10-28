<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login;
use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends Login
{
    public function getHeading(): string|Htmlable|null
    {

        return __('filament.admin_login_heading');
    }
}
