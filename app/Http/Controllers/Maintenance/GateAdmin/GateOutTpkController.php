<?php

namespace App\Http\Controllers\Maintenance\GateAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GateOutTpkController extends Controller
{
    public function index()
    {
        return view('maintenance.gate_admin.gate_out_tpk');
    }

    public function handleRequest(Request $request)
    {
        // Handle the request logic here
        // This is a placeholder for the actual implementation
        return response()->json(['message' => 'Request handled successfully']);
    }
}
