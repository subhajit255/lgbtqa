<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogError;
use Exception;
use Illuminate\Http\Request;

class LogErrorController extends Controller
{
    /**
     * Display a listing of the error logs.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $details = LogError::orderBy('created_at', 'desc')->paginate(10);

        return view('admin.log_error.index', compact('details'));
    }

    /**
     * Remove the specified error log from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $log = LogError::findOrFail($id);
            $log->delete();

            return redirect()->back()->with('success', 'Error log deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Remove multiple error logs from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            if (empty($ids)) {
                return response()->json(['status' => false, 'message' => 'Please select at least one log to delete.']);
            }
            LogError::whereIn('id', $ids)->delete();

            return response()->json(['status' => true, 'message' => 'Selected error logs deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again.']);
        }
    }

    /**
     * Display the specified error log.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        $detail = LogError::findOrFail($id);

        return view('admin.log_error.view', compact('detail'));
    }
}
