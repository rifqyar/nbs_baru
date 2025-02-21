<?php

namespace App\Http\Controllers\Maintenance\CancelOperation;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\CancelOperation\CancelOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CancelReceivingController extends Controller
{
    protected $maintenance;

    public function __construct(CancelOperationService $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    function index()
    {
        return view('maintenance.canceloperation.receiving');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->maintenance->getTableDataReceiving($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function getNoContainer(Request $request)
    {
        $viewData = $this->maintenance->getNoContainerReceiving($request->term);
        return response()->json($viewData);
    }

    function getRequest(Request $request)
    {
        $viewData = $this->maintenance->getRequestReceiving($request->term);
        return response()->json($viewData);
    }

    function deleteOperation(Request $request)
    {
        $viewData = $this->maintenance->deleteOperationReceiving($request->term);
        return response()->json($viewData);
    }
}
