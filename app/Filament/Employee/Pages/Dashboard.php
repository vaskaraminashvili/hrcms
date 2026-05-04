<?php

namespace App\Filament\Employee\Pages;

use App\Filament\Employee\Resources\EmployeeProfileResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $this->redirect(EmployeeProfileResource::getUrl('edit'));
    }
}
