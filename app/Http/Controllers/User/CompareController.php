<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Compare;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    function index()
    {
        $compares = Compare::whereUserId(auth()->id())->latest()->get();
        return view('user.compare', compact('compares'));
    }
    function clear_compare()
    {
        Compare::whereUserId(auth()->id())->delete();
        return redirect()->route('user.compare')->with('success', 'Compare empty successfully');
    }
    function remove_compare($id)
    {
        Compare::findOrFail($id);
        return redirect()->back()->with('success', 'Compare remove successfully');
    }
}
