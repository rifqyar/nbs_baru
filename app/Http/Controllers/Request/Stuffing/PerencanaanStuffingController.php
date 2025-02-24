<?php

namespace App\Http\Controllers\Request\Stuffing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Request\Stuffing\PerencaanService;
use Illuminate\Http\JsonResponse;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Exception;
use App\Traits\NpwpCheckPengkinianTrait;
class PerencanaanStuffingController extends Controller
{

    use NpwpCheckPengkinianTrait;

    protected $stuffing;

    public function __construct(PerencaanService $stuffing)
    {
        $this->stuffing = $stuffing;
    }

    public function index()
    {
        $now = date("d/m/y");
        $jumlah_req = "SELECT COUNT(NO_REQUEST) JUMLAH FROM PLAN_REQUEST_STUFFING WHERE REPLACE(NO_REQUEST,'P','S') NOT IN (SELECT NO_REQUEST FROM REQUEST_STUFFING) AND EARLY_STUFFING IS NULL ";


        $total_req = "SELECT COUNT(DISTINCT REQUEST_STUFFING.NO_REQUEST) AS TOTAL
            FROM REQUEST_STUFFING, CONTAINER_STUFFING 
            WHERE REQUEST_STUFFING.NO_REQUEST = CONTAINER_STUFFING.NO_REQUEST
            AND TRUNC(TGL_REQUEST) = TRUNC(SYSDATE)
            AND EARLY_STUFFING IS NULL";

        $total_cont = "SELECT COUNT(NO_CONTAINER) TOTAL FROM REQUEST_STUFFING, CONTAINER_STUFFING
        WHERE REQUEST_STUFFING.NO_REQUEST= CONTAINER_STUFFING.NO_REQUEST AND CONTAINER_STUFFING.TGL_APPROVE IS NOT NULL AND 
        TRUNC(TGL_REQUEST) = TRUNC(SYSDATE) AND EARLY_STUFFING IS NULL";

        // $data =  DB::connection('uster')->select($jumlah_req);
        // $data =  DB::connection('uster')->select($total_req);
        $jumlah =  DB::connection('uster')->selectOne($jumlah_req)->jumlah;
        $total =  DB::connection('uster')->selectOne($total_req)->total;
        $total_co = DB::connection('uster')->selectOne($total_cont)->total;


        return view('request.stuffing.plan.index', compact('jumlah', 'total_co', 'total'));
    }

    function storeStuffing(Request $request)
    {
        $validatedNpwp = $this->validateNpwpEMKL($request);

        // Check if the response is a failed validation JSON response
        if ($validatedNpwp instanceof \Illuminate\Http\JsonResponse) {
            return $validatedNpwp; // Return error response if NPWP validation failed
        }
        
        
        try {
            return $this->stuffing->storeStuffingPlan($request);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function overview(Request $request)
    {
        $request->validate([
            'no_req' => 'required',
        ]);

        $list = $this->stuffing->overviewStuffingPlan($request->no_req);

       
        return view('request.stuffing.plan.overview', $list);
    }

    public function add(Request $request)
    {
        return view('request.stuffing.plan.add');
    }

    public function view(Request $request)
    {
        $request->validate([
            'no_req' => 'required',
        ]);


        $list = $this->stuffing->viewStuffingPlan($request->no_req);
        
        
        return view('request.stuffing.plan.view', $list);
    }


    public function datatable(Request $request): JsonResponse
    {
        $listStuffing = $this->stuffing->listPerencanaanStuffing($request);
        return Datatables::of($listStuffing)
            ->addColumn('action', function ($listStuffing) {
                return $this->stuffing->checkNotaPerencaaanStuffing($listStuffing->no_request);
            })
            ->make(true);
    }

    public function masterVesselPalapa(Request $request): JsonResponse
    {
        $viewData = $this->stuffing->getVesselPalapa($request);
        return response()->json($viewData);
    }

    public function getContainerTPKByName(Request $request): JsonResponse
    {
        $viewData = $this->stuffing->getVesselPalapa($request);
        return response()->json($viewData);
    }

    public function getContainerByName(Request $request): JsonResponse
    {
        $viewData = $this->stuffing->getContainerByName($request);
        return response()->json($viewData);
    }

    public function getCommodityByName(Request $request): JsonResponse
    {
        $viewData = $this->stuffing->getCommodityByName($request);
        return response()->json($viewData);
    }

    public function getTanggalStack(Request $request)
    {
        $viewData = $this->stuffing->getTanggalStack($request);
        return json_encode($viewData);
    }

    public function CheckCapacityTPK(Request $request)
    {
        $viewData = $this->stuffing->CheckCapacityTPK($request);
        return $viewData;
    }

    public function addContainer(Request $request)
    {
        $viewData = $this->stuffing->addContainer($request);
        return $viewData;
    }

    public function deleteContainer(Request $request)
    {
        $viewData = $this->stuffing->deleteContainer($request);
        return $viewData;
    }

    public function containerApprove(Request $request)
    {
        $viewData = $this->stuffing->containerApprove($request);
        return $viewData;
    }

    public function getPmbByName(Request $request): JsonResponse
    {
        $viewData = $this->stuffing->GetPmbByName($request);
        return response()->json($viewData);
    }

    public function infoContainerStuffingPlan()
    {
        $data = $this->stuffing->getInfoStuffingPlan();

        return view('request.stuffing.plan.info', $data);
    }

    public function listContainer(Request $request, $no_req)
    {
        $listStuffing = $this->stuffing->listContainerOverview($no_req);

        if ($request->active == 'overview') {
            return Datatables::of($listStuffing)
                ->addColumn('tgl_approve_input', function ($listStuffing) {
                    if (in_array(Session::get('id_group'), [5, 1])) {
                        return '<input class="form-control" type="text" id="TGL_APPROVE" name="TGL_APPROVE"
                    value="' . $listStuffing->approve . '" placeholder="' . $listStuffing->approve . '" />';
                    } else {
                        return '<input class="form-control"  type="text" name="TGL_APPROVE" value="' . $listStuffing->approve . '"
                    placeholder="' . $listStuffing->approve . '" readonly="readonly" />';
                    }
                })
                ->make(true);
        } else if ($request->active == 'view') {
            return Datatables::of($listStuffing)
                ->addColumn('tgl_approve_input', function ($listStuffing) {
                    return '<input class="form-control"  type="text" name="TGL_APPROVE" value="' . $listStuffing->approve . '"
                    placeholder="' . $listStuffing->approve . '" readonly="readonly" />';
                })
                ->addColumn('approve_input', function ($listStuffing) {
                    $container = $this->stuffing->GetContainerStuffingById($listStuffing->no_request, $listStuffing->no_container);
                    if (!isset($container->no_request)) {
                        return '<button id="demo8" class="btn btn-primary w-100" onclick="update_tgl_approve(\'' . $listStuffing->no_container . '\', $(\'#TGL_APPROVE\').val(), \'' . $listStuffing->asal_cont . '\');">Approve</button>' .
                            '<button class="btn btn-info w-100" onclick="info_lapangan();">Info</button>';
                    } else {
                        return '<button id="demo8" class="btn btn-danger w-100" disabled>Approved</button>' .
                            '<button class="btn btn-info w-100" onclick="info_lapangan();">Info</button>';
                    }
                })
                ->addColumn('delete_input', function ($listStuffing) {

                    return '<button class="btn btn-danger w-100" onclick="del_cont(\'' . $listStuffing->no_container . '\', \'' . $listStuffing->no_req_sp2 . '\')">Hapus</button>';
                })
                ->make(true);
        }
    }
}
