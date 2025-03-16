<?php

namespace App\Http\Controllers\Request\Delivery;

use App\Http\Controllers\Controller;
use App\Services\Request\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Traits\NpwpCheckPengkinianTrait;
use Exception;

class DeliveryKeLuarController extends Controller
{

    use NpwpCheckPengkinianTrait;
    protected $delivery;

    public function __construct(DeliveryService $delivery)
    {
        $this->delivery = $delivery;
    }

    function index()
    {
        return view('request.delivery.delivery-ke-luar.index');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->delivery->dataDeliveryLuar($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function view(Request $request)
    {
        $viewData = $this->delivery->view($request->no_req);
        $list['data'] = $viewData;
        return view('request.delivery.delivery-ke-luar.view', $list);
    }

    function edit(Request $request)
    {
        $viewData = $this->delivery->edit($request->no_req);
        $list['data'] = $viewData;
        return view('request.delivery.delivery-ke-luar.edit', $list);
    }
    function editDataTables(Request $request): JsonResponse
    {
        $listDelivery = $this->delivery->editDataTables($request->noReq);
        return DataTables::of($listDelivery)->make(true);
    }

    function deleteEditDataTables(Request $request)
    {
        $viewData = $this->delivery->deleteEditDataTables($request);
        return response()->json($viewData);
    }

    function getNoContainer(Request $request)
    {
        $viewData = $this->delivery->getNoContainer($request->term);
        return response()->json($viewData);
    }

    function getTglStack(Request $request)
    {
        $viewData = $this->delivery->getTglStack($request->noCont);
        return response()->json($viewData);
    }

    function addContDeliveryLuar(Request $request)
    {
        $viewData = $this->delivery->addContDeliveryLuar($request);
        return response()->json($viewData);
    }

    function saveEditDeliveryLuar(Request $request)
    {
        $viewData = $this->delivery->saveEditDeliveryLuar($request);
        return response()->json($viewData);
    }

    function saveDeliveryLuar(Request $request)
    {
        $validatedNpwp = $this->validateNpwp($request);

        // Check if the response is a failed validation JSON response
        if ($validatedNpwp instanceof \Illuminate\Http\JsonResponse) {
            return $validatedNpwp; // Return error response if NPWP validation failed
        }

        $validatePconnect = pconnectIntegration($request->ACC_EMKL);
        if ($validatePconnect != 'MATCH') {
            if ($validatePconnect == '404') {
                return response()->json([
                    'status' => [
                        'code' => 404,
                        'msg' => 'Data Customer tidak ditemkukan di PConnect'
                    ],
                ]);
                // throw new Exception('Data Customer tidak ditemukan di PConnect', 400);
            } else if ($validatePconnect == 'BELUM PENGKINIAN NPWP') {
                return response()->json([
                    'status' => [
                        'code' => 400,
                        'msg' => 'Customer belum melakukan pengkinian data NPWP di Pconnect'
                    ],
                ]);
                throw new Exception('Customer belum melakukan pengkinian data NPWP di Pconnect', 400);
            }
        }

        $viewData = $this->delivery->saveDeliveryLuar($request);
        return response()->json($viewData);
    }

    function updateDataDelivery(Request $request)
    {
        $viewData = $this->delivery->updateDataDelivery($request);
        return response()->json($viewData);
    }

    function add()
    {
        return view('request.delivery.delivery-ke-luar.add');
    }

    function pbm(Request $request)
    {
        $viewData = $this->delivery->pbm($request->term);
        return response()->json($viewData);
    }
    function masterPelabuhanPalapa(Request $request)
    {
        $viewData = $this->delivery->masterPelabuhanPalapa($request->term);
        return response()->json($viewData);
    }

    function masterVesselPalapa(Request $request)
    {
        $viewData = $this->delivery->masterVesselPalapa($request->term);
        return response()->json($viewData);
    }
    function getTglStack2(Request $request)
    {
        $viewData = $this->delivery->getTglStack2($request);
        return response()->json($viewData);
    }

    function commodity(Request $request)
    {
        $viewData = $this->delivery->commodity($request->term);
        return response()->json($viewData);
    }
}
