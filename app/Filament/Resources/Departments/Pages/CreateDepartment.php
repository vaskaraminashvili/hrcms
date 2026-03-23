<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    /**
     * The parent department passed via ?record= query param.
     */
    public ?Model $parentRecord = null;

    public function mount(): void
    {
        // Resolve the parent from the query string (?record=<id>)
        $parentId = request()->query('record');

        if ($parentId) {
            $this->parentRecord = Department::findOrFail($parentId);
        }

        parent::mount();
    }

    /**
     * Pre-fill parent_id so the form carries it silently.
     */
    protected function getFillable(): array
    {
        return array_merge(
            parent::getFillable(),
            $this->parentRecord ? ['parent_id' => $this->parentRecord->getKey()] : []
        );
    }

    /**
     * Inject parent_id into the data before the record is created.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($this->parentRecord) {
            $data['parent_id'] = $this->parentRecord->getKey();
        }

        return $data;
    }

    /**
     * Bypass Filament's parent-resource association logic.
     * We set parent_id directly via mutateFormDataBeforeCreate,
     * so we just save the record normally.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);
        $record->save();

        return $record;
    }

    /**
     * Show the parent name in the page heading.
     */
    public function getHeading(): string
    {
        if ($this->parentRecord) {
            return "Add child to: {$this->parentRecord->name}";
        }

        return 'Create Department';
    }

    /**
     * Go back to the tree after saving.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * Optional: add a "Back to tree" button in the header.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToTree')
                ->label(__('filament.create_department.back_to_tree'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }
}
