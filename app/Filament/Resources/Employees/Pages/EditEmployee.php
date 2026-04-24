<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Enums\PersonalFile;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    public static string|Alignment $formActionsAlignment = Alignment::Center;

    protected function resolveRecord(int|string $key): Model
    {
        $record = parent::resolveRecord($key);

        $relationships = array_map(
            fn (PersonalFile $case) => $case->relationship(),
            PersonalFile::cases(),
        );

        $mediaCounts = [];
        foreach (PersonalFile::cases() as $case) {
            $mediaCounts[sprintf('media as %s', $case->tabBadgeMediaCountAttribute())] =
                fn ($query) => $query->where('collection_name', $case->mediaCollectionName());
        }

        $record->loadCount(array_merge($relationships, $mediaCounts));

        return $record;
    }

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
