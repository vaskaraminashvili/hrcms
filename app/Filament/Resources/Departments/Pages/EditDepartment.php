<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditDepartment extends EditRecord
{
    protected int $maxDepth = 6;

    protected static string $resource = DepartmentResource::class;

    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        $record = $this->getRecord();

        if (! $record instanceof Department) {
            return parent::getBreadcrumbs();
        }

        $resource = static::getResource();
        $breadcrumbs = [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
        ];

        foreach ($record->ancestors()->get() as $ancestor) {
            $breadcrumbs[$resource::getUrl('edit', ['record' => $ancestor])] = $ancestor->name;
        }

        $breadcrumbs[] = $record->name;

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $newParentId = $data['parent_id'] ?? null;

        if (filled($newParentId) && $newParentId != $record->parent_id) {
            $newParent = Department::find($newParentId);

            if ($newParent) {
                $newParentLevel = $newParent->ancestors()->count() + 1;
                $maxDescendantDepth = $record->descendants()->max('depth') ?? 0;
                $requiredDepth = $newParentLevel + 1 + $maxDescendantDepth;

                if ($requiredDepth > $this->maxDepth) {
                    Notification::make()
                        ->title('Cannot move department')
                        ->body("This move would exceed the maximum depth of {$this->maxDepth} levels (deepest node would reach level {$requiredDepth}).")
                        ->danger()
                        ->send();

                    throw new Halt;
                }
            }
        }

        $record->update($data);

        return $record;
    }
}
