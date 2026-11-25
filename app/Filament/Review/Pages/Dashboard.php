<?php

namespace App\Filament\Review\Pages;

use App\Filament\Review\Resources\Employees\EmployeeResource;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;

class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        throw new HttpResponseException(
            new RedirectResponse(EmployeeResource::getUrl('index', panel: 'review')),
        );
    }
}
