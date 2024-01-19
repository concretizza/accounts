<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UsersMeController extends Controller
{
    public function show()
    {
        return User::findOrFail(Auth::user()->id);
    }
}
