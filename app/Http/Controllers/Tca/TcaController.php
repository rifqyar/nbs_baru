<?php

namespace App\Http\Controllers\Tca;

use App\Http\Controllers\Controller;
use App\Services\Tca\TcaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TcaController extends Controller
{
    protected $tca;

    public function __construct(TcaService $tca)
    {
        $this->tca = $tca;
    }

    function index()
    {
        return view('tca.tidcontainerassociation');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->tca->DataTable($request);
        $dataTable =  DataTables::of($listDelivery)->make(true);
        // Check if the data is null
        if ($dataTable->getData() === null) {
            // Return a custom response indicating no data was found
            return response()->json(['data' => [], 'message' => 'No data found'], 200);
        }

        // Return the DataTable
        return $dataTable;
    }

    function getTruckList(Request $request)
    {
        $viewData = $this->tca->getTruckList($request->term);
        return response()->json($viewData);
    }

    function invoiceNumberPraya(Request $request)
    {
        $viewData = $this->tca->invoiceNumberPraya($request->term);
        return response()->json($viewData);
    }

    function saveAssociatePraya(Request $request)
    {
        $viewData = $this->tca->saveAssociatePraya($request);
        return $viewData;
    }
}
