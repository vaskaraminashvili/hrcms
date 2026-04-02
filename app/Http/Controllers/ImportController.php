<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function importEmployees()
    {
        $importData = DB::table('import_employees')->get();
        dd($importData);
    }
}
