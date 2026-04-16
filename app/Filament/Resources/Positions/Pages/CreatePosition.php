<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Services\PositionFormPersistence;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

class CreatePosition extends CreateRecord
{
    protected static string $resource = PositionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PositionFormPersistence::prepareDataForCreate($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return PositionFormPersistence::createFromValidatedData($data, $this->getParentRecord());
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'positions_employee_id_department_id_place_id_unique')) {
                throw $exception;
            }

            Notification::make()
                ->danger()
                ->title(__('filament.admin.position_resource.duplicate_employee_department_title'))
                ->body(__('filament.admin.position_resource.duplicate_employee_department_body'))
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }
    }

    public function form(Schema $schema): Schema
    {
        return PositionForm::configure($schema, false);
    }
}
