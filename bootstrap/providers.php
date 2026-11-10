<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\EmployeePanelProvider;
use App\Providers\Filament\ReviewPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    EmployeePanelProvider::class,
    ReviewPanelProvider::class,
];
