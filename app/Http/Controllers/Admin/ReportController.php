<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Report;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'PENDING');
        $details = Report::with('reporter', 'reportable')
            ->where('status', $status)
            ->latest()
            ->paginate(10);
            
        return view('admin.report.index', compact('details', 'status'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:reports,id',
            'status' => 'required|in:PENDING,REVIEWED,ACTION_TAKEN,DISMISSED',
            'admin_notes' => 'nullable|string',
        ]);

        $report = Report::find($request->id);
        $report->status = $request->status;
        if ($request->has('admin_notes')) {
            $report->admin_notes = $request->admin_notes;
        }
        $report->save();

        return response(['status' => true, 'message' => 'Report status updated successfully!', 'url' => route('admin.report.list')]);
    }
}
