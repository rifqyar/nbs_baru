<?php

namespace App\Http\Controllers\Tca;

use App\Http\Controllers\Controller;
use App\Services\Tca\TcaReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TcaReportController extends Controller
{
    protected $tca;

    public function __construct(TcaReportService $tca)
    {
        $this->tca = $tca;
    }

    function index()
    {
        return view('tca.tcareport');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->tca->DataTable($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function masterVessel(Request $request)
    {
        $viewData = $this->yca->masterVessel($request->term);
        return response()->json($viewData);
    }

    function generateExcel(Request $request)
    {

        $jenis        = $request->jenis;
        $tgl_awal    = $request->tgl_awal;
        $tgl_akhir    = $request->tgl_akhir;

        $tanggal = date("dmY");

        $query_list_     = "SELECT * FROM (
            SELECT CONTAINER_STUFFING.NO_CONTAINER , REQUEST_STUFFING.NO_REQUEST , REQUEST_STUFFING.TGL_REQUEST, V_MST_PBM.NM_PBM, 'STUFFING' KEGIATAN, CONTAINER_STUFFING.TGL_APPROVE, 
            CONTAINER_STUFFING.TGL_REALISASI
            FROM REQUEST_STUFFING INNER JOIN 
            CONTAINER_STUFFING ON REQUEST_STUFFING.NO_REQUEST = CONTAINER_STUFFING.NO_REQUEST
            LEFT JOIN V_MST_PBM ON REQUEST_STUFFING.KD_CONSIGNEE = V_MST_PBM.KD_PBM
            WHERE TRUNC(CONTAINER_STUFFING.TGL_APPROVE) BETWEEN TO_DATE('$tgl_awal','yyyy-mm-dd') AND TO_DATE('$tgl_akhir','yyyy-mm-dd')  
            UNION
            SELECT DISTINCT CONTAINER_STRIPPING.NO_CONTAINER , REQUEST_STRIPPING.NO_REQUEST , REQUEST_STRIPPING.TGL_REQUEST,  V_MST_PBM.NM_PBM, 'STRIPPING' KEGIATAN, CONTAINER_STRIPPING.TGL_APPROVE,
            CONTAINER_STRIPPING.TGL_REALISASI
            FROM REQUEST_STRIPPING INNER JOIN 
            CONTAINER_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = CONTAINER_STRIPPING.NO_REQUEST
            LEFT JOIN V_MST_PBM ON REQUEST_STRIPPING.KD_CONSIGNEE = V_MST_PBM.KD_PBM
            WHERE TRUNC(CONTAINER_STRIPPING.TGL_APPROVE) BETWEEN TO_DATE('$tgl_awal','yyyy-mm-dd') AND TO_DATE('$tgl_akhir','yyyy-mm-dd')) A  
            WHERE A.KEGIATAN LIKE '%$jenis%' ORDER BY NO_REQUEST DESC";

        $row_list        = DB::connection('uster')->select($query_list_);

        $tanggal = date("dmY");
        $data = [
            'row_list' => $row_list,
            'jenis' => $jenis,
            'tanggal' => $tanggal
        ];


        return view('tca.container.area.toexcel.toexcel', $data);
    }
}
