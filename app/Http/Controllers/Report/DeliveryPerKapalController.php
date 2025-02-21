<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\DeliveryPerKapalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DeliveryPerKapalController extends Controller
{
    protected $report;

    public function __construct(DeliveryPerKapalService $report)
    {
        $this->report = $report;
    }

    function index()
    {
        return view('report.deliveryperkapal.deliveryperkapal');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->report->DataTable($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function masterVessel(Request $request)
    {
        $viewData = $this->report->masterVessel($request->term);
        return response()->json($viewData);
    }

    function generateExcel(Request $request)
    {

        $nm_kapal     = $request->nm_kapal;
        $voyage_in         = $request->voyage;
        // $no_booking = $request->no_booking;
        $status        = $request->status;

        $tanggal = date("dmY");

        if ($status == NULL) {
            $query_status = '';
        } else {
            if ($status == 'FCL') {
                $query_status = "and b.status = 'FCL'";
            } else if ($status == 'MTY') {
                $query_status = "and b.status = 'MTY'";
            } else if ($status == 'LCL') {
                $query_status = "and b.status = 'LCL'";
            } else {
                $query_status = "";
            }
        }

        $query = "SELECT
        b.no_container,
        mc.size_,
        mc.type_,
        a.no_request,
        b.status,
        b.via,
        c.nopol,
        TO_CHAR(c.tgl_in, 'dd/mm/rrrr hh:ii:ss') tgl_in,
        nvl((SELECT username FROM master_user WHERE to_char(id)= to_char(c.id_user)), c.id_user) username,
        n.no_faktur no_nota,
        n.lunas
    FROM
        request_delivery a,
        container_delivery b,
        master_container mc,
        border_gate_out c,
        nota_delivery n
    WHERE
        a.no_request = b.no_request
        AND b.no_request = c.no_request(+)
        AND b.no_container = c.no_container(+)
        AND b.no_container = mc.no_container
        AND a.no_request = n.no_request(+)
        AND n.status NOT IN ('BATAL')
        $query_status
        AND a.VESSEL = '$nm_kapal'
        AND a.O_VOYIN = '$voyage_in'
    ORDER BY
        c.tgl_in DESC";

        $row_list        = DB::connection('uster')->select($query);

        $data = [
            'nm_kapal' => $nm_kapal,
            'voyage' => $voyage_in,
            'row_q' => $row_list,
            'tanggal' => $tanggal
        ];


        return view('report.deliveryperkapal.toexcel.toexcel', $data);
    }
}
