<?php

namespace App\Http\Controllers\Koreksi;

use App\Http\Controllers\Controller;
use App\Services\Koreksi\BatalSp2Service;
use Illuminate\Http\Request;

class BatalSp2Controller extends Controller
{
    protected $koreksi;

    public function __construct(BatalSp2Service $koreksi)
    {
        $this->koreksi = $koreksi;
    }

    function index()
    {
        return view('koreksi.batalsp2.index');
    }

    function getNoContainer(Request $request)
    {
        $viewData = $this->koreksi->getNoContainer($request->term);
        return response()->json($viewData);
    }

    function batalSp2Cont(Request $request)
    {
        $viewData = $this->koreksi->batalSp2Cont($request);
        return response()->json($viewData);
    }
}
