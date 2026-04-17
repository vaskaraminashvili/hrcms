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
            // Action::make('saveWithArchive')
            //     ->label(__('filament.department.save_and_archive'))
            //     ->color('primary')
            //     ->requiresConfirmation()
            //     ->modalHeading(__('filament.department.modal_archive_heading'))
            //     ->modalDescription(__('filament.department.modal_archive_description'))
            //     ->modalSubmitActionLabel(__('filament.department.modal_submit'))
            //     ->action(function (DepartmentArchiveService $service) {
            //         $formData = $this->form->getState();
            //         $originalData = $this->record->only(['name', 'parent_id']);

            //         $nameChanged = ($originalData['name'] ?? null) !== ($formData['name'] ?? null);
            //         $parentChanged = ($originalData['parent_id'] ?? null) !== ($formData['parent_id'] ?? null);
            //         if ($nameChanged || $parentChanged) {
            //             // Archive original + duplicate with new data
            //             $newDepartment = $service->archiveAndReplicate($this->record, $formData);

            //             // Full page redirect to the new record's edit page
            //             return redirect(
            //                 DepartmentResource::getUrl('edit', ['record' => $newDepartment])
            //             );
            //         } else {
            //             // No relevant change — just do a normal save
            //             $this->save();
            //         }
            //     }),

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
                            ->title(__('filament.department.cannot_delete_title'))
                            ->body(__('filament.department.cannot_delete_body', ['count' => $count]))
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
                ->label(__('filament.department.save_and_archive'))
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('filament.department.modal_archive_heading'))
                ->modalDescription(__('filament.department.modal_archive_description'))
                ->modalSubmitActionLabel(__('filament.department.modal_submit'))
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
                ->label(__('filament.department.cancel'))
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
                        ->title(__('filament.department.cannot_move_title'))
                        ->body(__('filament.department.cannot_move_body', [
                            'max_depth' => $this->maxDepth,
                            'required_depth' => $requiredDepth,
                        ]))
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
