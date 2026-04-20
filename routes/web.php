<?php

use App\Http\Controllers\DepartmentTypeCountController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::controller(ImportController::class)->group(function () {
    Route::get('/import-employees', 'importEmployees')->name('import.employees');
    Route::get('/import-positions', 'importPositions')->name('import.positions');
    Route::prefix('/import-personal-data')->group(function () {
        Route::get('/computer-skills', 'importComputerSkills')->name('import.computer-skills');
        // Route::get('/languages', 'importLanguages')->name('import.languages');
        // Route::get('/certificates', 'importCertificates')->name('import.certificates');
        // Route::get('/publications', 'importPublications')->name('import.publications');
        // Route::get('/projects', 'importProjects')->name('import.projects');
        // Route::get('/awards', 'importAwards')->name('import.awards');
        // Route::get('/memberships', 'importMemberships')->name('import.memberships');
        // Route::get('/patents', 'importPatents')->name('import.patents');
    });
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/departments/{department}/type-counts', [DepartmentTypeCountController::class, 'show'])
        ->name('departments.type-counts');
});
