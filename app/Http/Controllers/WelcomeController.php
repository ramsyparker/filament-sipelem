<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $fields = Field::orderBy('name')->get();
        return view('welcome', compact('fields'));
    }
}