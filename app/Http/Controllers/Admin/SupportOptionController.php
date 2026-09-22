<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportOption;
use Illuminate\Http\Request;


class SupportOptionController extends Controller
{
    public function index(Request $request)
    {
        $details = SupportOption::latest()->paginate(10);
        return view('admin.support-option.index', compact('details'));
    }

    public function addOrUpdate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:one_time,monthly',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'label' => 'required|string',
        ]);

        $id = $request->id;
        $postData = [
            'type' => $request->type,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'label' => $request->label,
        ];

        SupportOption::updateOrCreate(['id' => $id], $postData);

        $message = $id ? 'Support Option Updated Successfully' : 'Support Option Added Successfully';
        return response(['status' => true, 'message' => $message, 'url' => route('admin.support-option.list')]);
    }
}
