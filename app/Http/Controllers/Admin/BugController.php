<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use Illuminate\Http\Request;

class BugController extends Controller
{
    /**
     * Display a listing of reported bugs.
     */
    public function index()
    {
        $bugs = Bug::with('user')->latest()->paginate(10);
        return view('admin.bug.list', compact('bugs'));
    }

    /**
     * Display the specified bug details.
     */
    public function view($id)
    {
        $bug = Bug::with('user')->findOrFail($id);
        return view('admin.bug.view', compact('bug'));
    }

    /**
     * Update the status of the bug.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,working progress,resolve',
        ]);

        $bug = Bug::findOrFail($id);
        $bug->status = $request->status;
        $bug->save();

        return redirect()->back()->with('success', 'Bug status updated successfully.');
    }
}
