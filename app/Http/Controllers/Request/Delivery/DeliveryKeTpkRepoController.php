<?php

namespace App\Http\Controllers\Request\Delivery;

use App\Http\Controllers\Controller;
use App\Services\Request\DeliveryKeTpkRepoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\NpwpCheckPengkinianTrait;
class DeliveryKeTpkRepoController extends Controller
{
    use NpwpCheckPengkinianTrait;
    protected $deliveryTpk;

    public function __construct(DeliveryKeTpkRepoService $deliveryTpk)
    {
        $this->deliveryTpk = $deliveryTpk;
    }

    function index()
    {
        return view('request.delivery.delivery-ke-luar-tpk.index');
    }

    function add()
    {
        return view('request.delivery.delivery-ke-luar-tpk.add');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->deliveryTpk->dataDeliveryLuar($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function view(Request $request)
    {
        $viewData = $this->deliveryTpk->view($request->no_req);
        $list['data'] = $viewData;
        return view('request.delivery.delivery-ke-luar-tpk.view', $list);
    }

    function edit(Request $request)
    {
        $viewData = $this->deliveryTpk->edit($request->no_req, $request->no_req2);
        $list['data'] = $viewData;
        return view('request.delivery.delivery-ke-luar-tpk.edit', $list);
    }

    function cekEarly(Request $request)
    {
        $viewData = $this->deliveryTpk->cekEarly($request);
        return response()->json($viewData);
    }

    function editDo(Request $request)
    {
        $validatedNpwp = $this->validateNpwp($request);

        // Check if the response is a failed validation JSON response
        if ($validatedNpwp instanceof \Illuminate\Http\JsonResponse) {
            return $validatedNpwp; // Return error response if NPWP validation failed
        }
        
        $viewData = $this->deliveryTpk->editDo($request);
        return response()->json($viewData);
    }

    function contDelivery(Request $request)
    {
        $viewData = $this->deliveryTpk->contDelivery($request);
        return response()->json($viewData);
    }

    function carrierPraya(Request $request)
    {
        $viewData = $this->deliveryTpk->carrierPraya($request);
        return response()->json($viewData);
    }
    function cekNoCont(Request $request)
    {
        $viewData = $this->deliveryTpk->cekNoCont($request);
        return response()->json($viewData);
    }
    function addCont(Request $request)
    {
        $viewData = $this->deliveryTpk->addCont($request);
        return response()->json($viewData);
    }

    function editContList(Request $request): JsonResponse
    {
        $listDelivery = $this->deliveryTpk->editContList($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function delCont(Request $request): JsonResponse
    {
        $viewData = $this->deliveryTpk->delCont($request);
        return response()->json($viewData);
    }

    function pbm(Request $request)
    {
        $viewData = $this->deliveryTpk->pbm($request->term);
        return response()->json($viewData);
    }
    
    function refcal(Request $request)
    {
        $viewData = $this->deliveryTpk->refcal($request->term);
        return response()->json($viewData);
    }

    function addDoTpk(Request $request)
    {
        $viewData = $this->deliveryTpk->addDoTpk($request->term);
        return response()->json($viewData);
    }
}
