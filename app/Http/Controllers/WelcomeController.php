<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Membership;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $fields = Field::orderBy('name')->get();
        $memberships = Membership::all();
        return view('welcome', compact('fields','memberships'));
    }
}