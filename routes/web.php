<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/import-employees', [ImportController::class, 'importEmployees'])->name('import.employees');
