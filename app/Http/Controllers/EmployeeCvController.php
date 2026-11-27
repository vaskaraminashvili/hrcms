<?php

namespace App\Http\Controllers;

use App\Enums\CvLocale;
use App\Models\Employee;
use App\Services\EmployeeCvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmployeeCvController extends Controller
{
    public function show(Request $request, Employee $employee, string $locale, EmployeeCvService $employeeCvService): View
    {
        $cvLocale = CvLocale::tryFromRoute($locale);
        abort_unless($cvLocale !== null, 404);

        Gate::authorize('viewCv', $employee);

        return view('cv.show', [
            ...$employeeCvService->buildViewData($employee, $cvLocale),
            'download' => $request->boolean('download'),
        ]);
    }
}
