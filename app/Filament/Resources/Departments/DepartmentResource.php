<?php

namespace App\Filament\Resources\Departments;

use App\Enums\DepartmentType;
use App\Filament\Resources\Departments\Fields\DepartmentStatusIconField;
use App\Filament\Resources\Departments\Fields\DepartmentTextField;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Openplain\FilamentTreeView\Fields\TextField;
use Openplain\FilamentTreeView\Tree;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

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
                DepartmentTextField::make('name'),
                // DepartmentTextField::make('vacancy_count')
                //     ->formatStateUsing(function (int $state, Department $record): string {
                //         if ($record->children()->exists()) {
                //             return '';
                //         }

                //         return 'Vacancies: '.$state;

                //     })
                //     ->alignEnd(),
                TextField::make('type')
                    ->formatStateUsing(function (mixed $state): string {
                        if ($state instanceof DepartmentType) {
                            return $state->getLabel();
                        }

                        return (string) ($state ?? '');
                    })
                    ->alignEnd(),
                DepartmentStatusIconField::make('status')
                    ->boolean()
                    ->icons('heroicon-o-check-circle', 'heroicon-o-archive-box')
                    ->colors('success', 'warning')
                    ->alignEnd(),
            ])
            ->recordActions([

                // Navigate to edit page
                CreateAction::make()
                    ->label('')
                    ->icon('heroicon-o-plus')
                    ->url(
                        fn (Department $record): string => static::getUrl('create', ['record' => $record])
                    ),
                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->url(
                        fn (Department $record): string => static::getUrl('edit', ['record' => $record])
                    ),
            ])
            ->maxDepth(6)
            ->reorderable(false)
        // ->modifyQueryUsing(function (Builder $query): Builder {
        //     $table = $query->getModel()->getTable();

        //     return $query->select([
        //         "{$table}.id",
        //         "{$table}.parent_id",
        //         "{$table}.order",
        //         "{$table}.name",
        //         "{$table}.slug",
        //         "{$table}.status",
        //         "{$table}.vacancy_count",
        //         "{$table}.color",
        //     ]);
        // })
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['children']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\TreeDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.departments.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.departments.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.departments.plural_model_label');
    }
}
