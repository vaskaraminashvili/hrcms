<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    public static string|Alignment $formActionsAlignment = Alignment::Center;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['birth_date'] = '1900-01-01'; // set the oldest date for the birth date

        return $data;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
