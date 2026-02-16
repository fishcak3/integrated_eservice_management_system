<?php

namespace App\Http\Controllers\resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResidentDashboardController extends Controller
{
    public function dashboard()
    {
        return view('userdashboard.forResident.dashboard');
    }
}
