<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
use App\Services\DepartmentArchiveService;
use Filament\Actions\Action;
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
            Action::make('saveWithArchive')
                ->label('Save & Archive')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Archive & Duplicate Department?')
                ->modalDescription(
                    'Changing the name or parent will archive this department and its positions, 
                 then create a new duplicate with your changes.'
                )
                ->modalSubmitActionLabel('Yes, proceed')
                ->action(function (DepartmentArchiveService $service) {
                    $formData = $this->form->getState();
                    $originalData = $this->record->only(['name', 'parent_id']);

                    $nameChanged = ($originalData['name'] ?? null) !== ($formData['name'] ?? null);
                    $parentChanged = ($originalData['parent_id'] ?? null) !== ($formData['parent_id'] ?? null);
                    if ($nameChanged || $parentChanged) {
                        // Archive original + duplicate with new data
                        $newDepartment = $service->archiveAndReplicate($this->record, $formData);

                        // Full page redirect to the new record's edit page
                        return redirect(
                            DepartmentResource::getUrl('edit', ['record' => $newDepartment])
                        );
                    } else {
                        // No relevant change — just do a normal save
                        $this->save();
                    }
                }),

            DeleteAction::make()
                ->before(function (DeleteAction $action): void {
                    $record = $this->getRecord();

                    if (! $record instanceof Department) {
                        return;
                    }

                    if ($record->positions()->exists()) {
                        $count = $record->positions()->count();

                        Notification::make()
                            ->danger()
                            ->title('Cannot delete department')
                            ->body("This department has {$count} position(s) assigned. Reassign or remove them first.")
                            ->send();

                        $action->halt();
                    }
                }),

        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('saveWithArchive')
                ->label('Save & Archive')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Archive & Duplicate Department?')
                ->modalDescription(
                    'Changing the name or parent will archive this department and its positions, 
             then create a new duplicate with your changes.'
                )
                ->modalSubmitActionLabel('Yes, proceed')
                ->action(function (DepartmentArchiveService $service) {
                    $formData = $this->form->getState();
                    $originalData = $this->record->only(['name', 'parent_id']);

                    $nameChanged = ($originalData['name'] ?? null) !== ($formData['name'] ?? null);
                    $parentChanged = ($originalData['parent_id'] ?? null) !== ($formData['parent_id'] ?? null);
                    if ($nameChanged || $parentChanged) {
                        // Archive original + duplicate with new data
                        $newDepartment = $service->archiveAndReplicate($this->record, $formData);

                        // Full page redirect to the new record's edit page
                        return redirect(
                            DepartmentResource::getUrl('edit', ['record' => $newDepartment])
                        );
                    } else {
                        // No relevant change — just do a normal save
                        $this->save();
                    }
                }),
            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url(DepartmentResource::getUrl('index')),

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
