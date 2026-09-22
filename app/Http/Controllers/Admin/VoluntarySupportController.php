<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\VoluntarySupport;

class VoluntarySupportController extends Controller
{
    public function index(Request $request)
    {
        $details = VoluntarySupport::with('user')->latest()->paginate(10);
        return view('admin.support.index', compact('details'));
    }
}
