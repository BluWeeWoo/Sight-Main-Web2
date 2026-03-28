<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index($id = null)
    {
        $patients = [
            (object)[
                'id' => 1,
                'name' => 'Emma Rodriguez',
                'last_check' => '2 hours ago'
            ],
            (object)[
                'id' => 2,
                'name' => 'John Smith',
                'last_check' => 'Yesterday'
            ],
        ];

        $selectedPatient = collect($patients)->firstWhere('id', $id);

        return view('dashboard', compact('patients','selectedPatient'));
    }
}