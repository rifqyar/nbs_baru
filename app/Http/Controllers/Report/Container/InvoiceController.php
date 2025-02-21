<?php

namespace App\Http\Controllers\Report\Container;

use App\Http\Controllers\Controller;
use App\Services\Report\Container\ContainerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    protected $report;

    public function __construct(ContainerService $report)
    {
        $this->report = $report;
    }

    function index()
    {
        return view('report.container.invoice.invoice');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->report->DataTable($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function nota(Request $request)
    {
        $viewData = $this->report->nota($request->term);
        return response()->json($viewData);
    }

    function generateExcel(Request $request)
    {


        $no_nota = $request->NO_NOTA;
        $no_request = $request->NO_REQUEST;
        $kegiatan = $request->KEGIATAN;

        $tanggal = date("dmY");

        if ($kegiatan == 'DELIVERY_MTY' || $kegiatan == 'RELOKASI_MTY_KE_TPK' || $kegiatan == 'RELOKASI_TPK_EKS_STUFFING' || $kegiatan == 'PENUMPUKAN_SP2') {
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
            'no_nota' => $no_nota,
            'no_request' => $no_request
        ];


        return view('report.container.invoice.toexcel.toexcel', $data);
    }

    function generatePdf(Request $request)
    {
        $data = array();
        $data =  $this->report->generatePdf($request);
        // if ($data == 'NOT_FOUND') {
        //     return redirect()->route('report.contstuffingby.topdf.topdf');
        // }
        $pdf = Pdf::loadView('report.container.invoice.topdf.topdf', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('ContainerInvoice.pdf');
    }
}
