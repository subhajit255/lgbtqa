<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResourceApiController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Resource::where('status', 'PUBLISHED');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }
        if ($request->has('language')) {
            $query->where('languages', 'LIKE', '%' . $request->language . '%');
        }
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get()
        ]);
    }
}
