<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Notifications\Notification;
use Openplain\FilamentTreeView\Fields\IconField;
use Openplain\FilamentTreeView\Fields\TextField;
use Openplain\FilamentTreeView\Tree;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Department';

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentsTable::configure($table);
    }

    public static function tree(Tree $tree): Tree
    {
        return $tree
            ->fields([
                TextField::make('name'),
                IconField::make('is_active')
                    ->alignEnd(),
            ])
            ->recordActions([

                // Navigate to edit page
                CreateAction::make()
                    ->url(fn (Department $record): string => static::getUrl('create', ['record' => $record])
                    ),

                // Edit in modal
                Action::make('editModal')
                    ->label('Quick Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (Department $record): array => [
                        'name' => $record->name,
                        'description' => $record->description,
                    ])
                    ->form([
                        TextInput::make('name')->required(),
                        Textarea::make('description'),
                    ])
                    ->action(function (Department $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('Category updated')
                            ->success()
                            ->send();
                    }),

                // Delete with descendant warning
                DeleteAction::make()
                    ->modalDescription(function (Category $record): string {
                        $count = $record->descendants()->count();

                        if ($count === 0) {
                            return 'Are you sure you want to delete this category?';
                        }

                        return "This category has {$count} descendants that will also be deleted.";
                    }),
            ])
            ->maxDepth(6);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\TreeDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
