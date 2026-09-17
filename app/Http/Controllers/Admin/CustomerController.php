<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('user')->withCount('user')->orderBy('created_at', 'desc')->get();

        return view('admin.customers.index', compact('customers'));
    }
}
