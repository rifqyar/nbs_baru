<?php

namespace App\Http\Controllers\Koreksi;

use App\Http\Controllers\Controller;
use App\Services\Koreksi\BatalReceivingService;
use Illuminate\Http\Request;

class BatalReceivingController extends Controller
{
    protected $koreksi;

    public function __construct(BatalReceivingService $koreksi)
    {
        $this->koreksi = $koreksi;
    }

    function index()
    {
        return view('koreksi.batalreceiving.index');
    }

    function getNoContainer(Request $request)
    {
        $viewData = $this->koreksi->getNoContainer($request->term);
        return response()->json($viewData);
    }

    function batalReceivingCont(Request $request)
    {
        $viewData = $this->koreksi->batalReceivingCont($request);
        return response()->json($viewData);
    }
}
