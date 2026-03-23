<?php

namespace App\Filament\Resources\VacationPolicies\Pages;

use App\Filament\Resources\VacationPolicies\VacationPolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVacationPolicies extends ListRecords
{
    protected static string $resource = VacationPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
