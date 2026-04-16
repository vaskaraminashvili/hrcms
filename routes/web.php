<?php

use App\Http\Controllers\DepartmentTypeCountController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/import-employees', [ImportController::class, 'importEmployees'])->name('import.employees');
Route::get('/import-positions', [ImportController::class, 'importPositions'])->name('import.positions');
Route::get('/import-images', [ImportController::class, 'importImages'])->name('import.images');

Route::middleware(['auth'])->group(function (): void {
    Route::get('/departments/{department}/type-counts', [DepartmentTypeCountController::class, 'show'])
        ->name('departments.type-counts');
});
