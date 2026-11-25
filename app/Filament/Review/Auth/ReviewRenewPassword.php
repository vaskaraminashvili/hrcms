<?php

namespace App\Filament\Review\Auth;

use App\Filament\Review\Resources\Employees\EmployeeResource;
use Yebor974\Filament\RenewPassword\Pages\Auth\RenewPassword;

class ReviewRenewPassword extends RenewPassword
{
    public function renew(): void
    {
        parent::renew();

        $this->redirect(EmployeeResource::getUrl('index', panel: 'review'));
    }
}
