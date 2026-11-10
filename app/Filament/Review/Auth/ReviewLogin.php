<?php

namespace App\Filament\Review\Auth;

use App\Filament\Review\Resources\Employees\EmployeeResource;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Illuminate\Contracts\Support\Htmlable;

class ReviewLogin extends Login
{
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response !== null) {
            session()->put('url.intended', EmployeeResource::getUrl('index', panel: 'review'));
        }

        return $response;
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('filament.review_panel.login_heading');
    }
}
