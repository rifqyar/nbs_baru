<?php

namespace App\Http\Controllers\Maintenance\CancelOperation;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\CancelOperation\CancelOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CancelDeliveryController extends Controller
{
    protected $maintenance;

    public function __construct(CancelOperationService $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    function index()
    {
        return view('maintenance.canceloperation.delivery');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->maintenance->getTableData($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function getNoContainer(Request $request)
    {
        $viewData = $this->maintenance->getNoContainer($request->term);
        return response()->json($viewData);
    }

    function getRequest(Request $request)
    {
        $viewData = $this->maintenance->getRequest($request->term);
        return response()->json($viewData);
    }

    function deleteOperation(Request $request)
    {
        $viewData = $this->maintenance->deleteOperation($request->term);
        return response()->json($viewData);
    }
}
