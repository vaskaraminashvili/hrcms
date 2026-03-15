<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use Openplain\FilamentTreeView\Resources\Pages\TreePage;

class TreeDepartments extends TreePage
{
    protected static string $resource = DepartmentResource::class;
}
