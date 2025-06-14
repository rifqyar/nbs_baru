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

        // Build the base query using Eloquent
        $query = DB::connection('uster')->table('request_delivery as a')
            ->join('container_delivery as b', 'a.no_request', '=', 'b.no_request')
            ->join('master_container as mc', 'b.no_container', '=', 'mc.no_container')
            ->leftJoin('border_gate_out as c', function ($join) {
                $join->on('b.no_request', '=', 'c.no_request')
                    ->on('b.no_container', '=', 'c.no_container');
            })
            ->leftJoin('nota_delivery as n', 'a.no_request', '=', 'n.no_request')
            ->select([
                'b.no_container',
                'mc.size_',
                'mc.type_',
                'a.no_request',
                'b.status',
                'b.via',
                'c.nopol',
                DB::raw("TO_CHAR(c.tgl_in, 'dd/mm/rrrr hh:ii:ss') as tgl_in"),
                DB::raw("COALESCE((SELECT username FROM master_user WHERE to_char(id)= to_char(c.id_user)), c.id_user) as username"),
                'n.no_faktur as no_nota',
                'n.lunas'
            ])
            ->where('a.VESSEL', $nm_kapal)
            ->where('a.O_VOYIN', $voyage_in)
            ->whereNotIn('n.status', ['BATAL'])
            ->orderByDesc('c.tgl_in');

        // Filter by status if provided
        if ($status == 'FCL' || $status == 'MTY' || $status == 'LCL') {
            $query->where('b.status', $status);
        }

        $row_list = $query->get();

        $data = [
            'nm_kapal' => $nm_kapal,
            'voyage' => $voyage_in,
            'row_q' => $row_list,
            'tanggal' => $tanggal
        ];


        return view('report.deliveryperkapal.toexcel.toexcel', $data);
    }
}
