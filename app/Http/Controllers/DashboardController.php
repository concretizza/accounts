<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $plan = [];

        return response()->json([
            'plan' => $plan,
        ]);
    }
}
