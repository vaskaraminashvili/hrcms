<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Fields\DepartmentTextField;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Openplain\FilamentTreeView\Fields\IconField;
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
                DepartmentTextField::make('name'),
                IconField::make('is_active')
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

                // Delete with descendant warning
                DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->modalDescription(function (Department $record): string {
                        $count = $record->descendants()->count();

                        if ($count === 0) {
                            return 'Are you sure you want to delete this department?';
                        }

                        return "This department has {$count} descendants that will also be deleted.";
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
