<?php

namespace App\Http\Controllers\Request\Delivery;

use App\Http\Controllers\Controller;
use App\Services\Request\PerpanjanganDeliveryKeLuarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PerpanjanganDeliveryKeLuar extends Controller
{
    protected $pDelivery;

    public function __construct(PerpanjanganDeliveryKeLuarService $pDelivery)
    {
        $this->pDelivery = $pDelivery;
    }

    function index()
    {
        return view('request.delivery.perpanjangan-delivery-ke-luar.index');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->pDelivery->dataDeliveryLuar($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function view(Request $request)
    {
        $viewData = $this->pDelivery->view($request->no_req);
        $list['data'] = $viewData;
        return view('request.delivery.perpanjangan-delivery-ke-luar.view', $list);
    }

    function contList(Request $request): JsonResponse
    {
        $listDelivery = $this->pDelivery->contList($request->noReq);
        return DataTables::of($listDelivery)->make(true);
    }

    function addDo(Request $request)
    {
        $viewData = $this->pDelivery->updatePerpanjangan($request);
        return response()->json($viewData);
    }

    function edit(Request $request)
    {
        $viewData = $this->pDelivery->edit($request->no_req, $request->no_req_old);
        $list['data'] = $viewData;
        return view('request.delivery.perpanjangan-delivery-ke-luar.edit', $list);
    }

    function editContList(Request $request): JsonResponse
    {
        $listDelivery = $this->pDelivery->editContList($request->noReq);
        return DataTables::of($listDelivery)->make(true);
    }

    function editDo(Request $request)
    {
        $viewData = $this->pDelivery->editPerpanjangan($request);
        return response()->json($viewData);
    }
}
