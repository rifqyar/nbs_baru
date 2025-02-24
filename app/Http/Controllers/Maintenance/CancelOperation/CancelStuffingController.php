<?php

namespace App\Http\Controllers\Maintenance\CancelOperation;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\CancelOperation\CancelOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CancelStuffingController extends Controller
{
    protected $maintenance;

    public function __construct(CancelOperationService $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    function index()
    {
        return view('maintenance.canceloperation.stripping');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->maintenance->getTableDataStripping($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function getNoContainer(Request $request)
    {
        $viewData = $this->maintenance->getNoContainerStripping($request->term);
        return response()->json($viewData);
    }

    function getRequest(Request $request)
    {
        $viewData = $this->maintenance->getRequestStripping($request->term);
        return response()->json($viewData);
    }

    function deleteOperation(Request $request)
    {
        $viewData = $this->maintenance->deleteOperationStripping($request->term);
        return response()->json($viewData);
    }
}
