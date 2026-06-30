<?php

namespace App\Filament\Resources\VacationPolicies\Pages;

use App\Filament\Resources\VacationPolicies\VacationPolicyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVacationPolicy extends EditRecord
{
    protected static string $resource = VacationPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
