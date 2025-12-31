<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('HomePage', [
            'customers' => Customer::with('Accounts')->orderBy("created_at", "DESC")->get()
        ]);
    }
}
