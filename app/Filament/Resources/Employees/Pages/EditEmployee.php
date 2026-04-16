<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    public static string|Alignment $formActionsAlignment = Alignment::Center;

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getTitle(): string
    {
        return __('filament.admin.edit_employee.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.admin.edit_employee.title');
    }
}
