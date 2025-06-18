<?php

namespace App\Http\Controllers\StagingPymad;

use App\Http\Controllers\Controller;
use App\Services\StagingPymad\StagingPymadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StagingPymadController extends Controller
{
    protected $staginpymad;

    public function __construct(StagingPymadService $staginpymad)
    {
        $this->staginpymad = $staginpymad;
    }

    function index()
    {
        return view('staginpymad.container.request.request');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->staginpymad->DataTable($request);
        return DataTables::of($listDelivery)->make(true);
    }

    // function nota(Request $request)
    // {
    //     $viewData = $this->staginpymad->notaRequest($request->term);
    //     return response()->json($viewData);
    // }

    function generateExcel(Request $request)
    {

        $no_request = $request->NO_REQUEST;
        $kegiatan = $request->KEGIATAN;

        $tanggal = date("dmY");

        $get_nm_kapal = "select vessel, voyage from request_delivery where no_request = '$no_request'";
        $rnm = DB::connection('uster')->selectOne($get_nm_kapal);
        $vessel = $rnm->vessel ?? '-';
        $voy = $rnm->voyage ?? '-';

        if ($kegiatan == 'DELIVERY') {
            $query = "select cd.no_container, cd.start_stack tgl_awal, cd.tgl_delivery tgl_akhir, mc.size_, mc.type_, cd.status, cd.hz, cd.komoditi, cd.berat
                    from container_delivery cd inner join master_container mc 
                    on cd.no_container = mc.no_container
                    where no_request = '$no_request'";
        } else if ($kegiatan == 'STRIPPING' || $kegiatan == 'RELOKASI_MTY_EKS_STRIPPING') {
            $query = "select cd.no_container, cd.tgl_bongkar tgl_awal, case when cd.tgl_selesai is null then cd.tgl_bongkar+4 else cd.tgl_selesai end tgl_akhir,
                    mc.size_, mc.type_, 'FCL' status , cd.hz, cd.commodity komoditi, '22000' berat
                    from container_stripping cd inner join master_container mc 
                    on cd.no_container = mc.no_container
                    where no_request = '$no_request'";
        } else if ($kegiatan == 'STUFFING') {
            $query = "select cd.no_container, cd.start_stack tgl_awal, cd.start_perp_pnkn tgl_akhir,
                    mc.size_, mc.type_, 'MTY' status , cd.hz, cd.commodity komoditi, cd.berat
                    from container_stuffing cd inner join master_container mc 
                    on cd.no_container = mc.no_container
                    where no_request = '$no_request'";
        } else if ($kegiatan == 'RECEIVING') {
            $query = "select cd.no_container, '' tgl_awal, '' tgl_akhir,
                    mc.size_, mc.type_, 'MTY' status , case when cd.hz is null then 'N' else cd.hz end hz,
                    cd.komoditi , case when mc.size_ = '20' then '2000' else '4000' end as berat
                    from container_receiving cd inner join master_container mc 
                    on cd.no_container = mc.no_container
                    where no_request = '$no_request'";
        } else if ($kegiatan == 'BATAL_MUAT') {
            $query = "select cd.no_container, cd.start_pnkn tgl_awal , cd.end_pnkn tgl_akhir,
                    mc.size_, mc.type_, cd.status status , 'N' hz,
                    '' komiditi , case when mc.size_ = '20' then '2000' else '4000' end as berat
                    from container_batal_muat cd inner join master_container mc 
                    on cd.no_container = mc.no_container
                    where no_request = '$no_request'";
        }

        $row_list        = DB::connection('uster')->select($query);

        $tanggal = date("dmY");
        $data = [
            'row_q' => $row_list,
            'kegiatan' => $kegiatan,
            'no_request' => $no_request,
            'vessel' => $vessel,
            'voy' => $voy
        ];


        return view('report.container.request.toexcel.toexcel', $data);
    }
}
