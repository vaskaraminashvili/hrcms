<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Enums\PersonalFile;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
            ActionGroup::make([
                Action::make('cvGeorgian')
                    ->label(__('cv.actions.georgian'))
                    ->url(fn (): string => route('employees.cv.show', [
                        'employee' => $this->getRecord(),
                        'locale' => 'ka',
                    ]))
                    ->openUrlInNewTab(),
                Action::make('cvEnglish')
                    ->label(__('cv.actions.english'))
                    ->url(fn (): string => route('employees.cv.show', [
                        'employee' => $this->getRecord(),
                        'locale' => 'en',
                    ]))
                    ->openUrlInNewTab(),
            ])
                ->label(__('cv.actions.generate'))
                ->color('primary')
                ->icon('heroicon-m-document-text'),
            Action::make('resetUserPassword')
                ->label('')
                ->icon('heroicon-m-key')
                ->color('danger')
                ->visible(fn (): bool => $this->canResetEmployeeUserPassword())
                ->requiresConfirmation()
                ->modalHeading(__('filament.admin.edit_employee.reset_password_modal_heading'))
                ->modalDescription(__('filament.admin.edit_employee.reset_password_modal_description'))
                ->action(function (): void {
                    $user = $this->getRecord()->user;

                    if ($user === null) {
                        Notification::make()
                            ->warning()
                            ->title(__('filament.admin.edit_employee.reset_password_no_user'))
                            ->send();

                        return;
                    }

                    $user->update([
                        'password' => (string) config('employees.default_password'),
                        'force_renew_password' => true,
                    ]);

                    Notification::make()
                        ->success()
                        ->title(__('filament.admin.edit_employee.reset_password_success'))
                        ->send();
                }),
        ];
    }

    protected function canResetEmployeeUserPassword(): bool
    {
        $authUser = Auth::user();
        if (! $authUser instanceof User) {
            return false;
        }

        if (! $authUser->hasRole(config('filament-shield.super_admin.name'))) {
            return false;
        }

        return $this->getRecord()->user_id !== null;
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
