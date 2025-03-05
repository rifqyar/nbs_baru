<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Services\Print\CetakSP2;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class CetakSP2Controller extends Controller
{
    protected $service;
    public function __construct(CetakSP2 $cetakSP2)
    {
        $this->service = $cetakSP2;
    }

    public function index()
    {
        return view('print.sp2.index');
    }

    public function data(Request $request)
    {
        $data = $this->service->getData($request->all());

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-info p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y') . '</span>';
            })
            ->editColumn('no_request', function ($data) {
                $html = "<b class='font-bold'> <span class='text-primary'>NO. REQUEST</span> : $data->no_request </b> <br>";
                $html .= "<b class='font-bold'> <span class='text-success'>NO. NOTA</span> : $data->no_nota </b> <br>";

                return $html;
            })
            ->editColumn('action', function ($data) {
                $btn = self::cekNota($data);
                return $btn;
            })
            ->rawColumns(['tgl_request', 'no_request', 'action'])
            ->make(true);
    }

    public function cekNota($data)
    {
        $html = '';
        $data = json_decode(json_encode($data), true);
        if ($data['lunas'] == "YES") {
            $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded w-100" href="' . route('uster.print.sp2.print', ['no_req' => base64_encode($data['no_request']), 'peralihan' => $data['peralihan']]) . '" target="_blank"> Cetak Kartu SP2  </a> <br/> ';
        } else if (($data['lunas'] == 0) && ($data['peralihan'] == 'T') && ($data['delivery_ke'] == 'TPK')) {
            $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded w-100" href="' . route('uster.print.sp2.print', ['no_req' => base64_encode($data['no_request']), 'peralihan' => $data['peralihan']]) . '" target="_blank"> Cetak Kartu SP2  </a> <br/> ';
        } else if (($data['lunas'] == 0) && ($data['peralihan'] == 'NOTA_KIRIM')) {
            $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded w-100" href="' . route('uster.print.sp2.print', ['no_req' => base64_encode($data['no_request']), 'peralihan' => $data['peralihan']]) . '" target="_blank"> Cetak Kartu SP2  </a> <br/> ';
        } else {
            $html .=  '<span class="text-danger">Nota Belum Lunas</span>';
        }

        return $html;
    }

    public function print(Request $request)
    {
        $name = Session::get('name');
        $no_req     = $request->no_req;
        $no_req = base64_decode($no_req);
        $peralihan = $request->peralihan;

        if ($peralihan == 'NOTA_KIRIM') {
            $query_list = "SELECT DISTINCT TO_CHAR(container_delivery.START_PERP) START_PERP, TO_CHAR(request_delivery.TGL_REQUEST) TGL_START,
            request_delivery.STATUS STATUS_REQ, REQUEST_DELIVERY.NO_REQUEST, emkl.NM_PBM AS NAMA_EMKL, CONTAINER_DELIVERY.STATUS, MASTER_CONTAINER.NO_CONTAINER,
            MASTER_CONTAINER.SIZE_, MASTER_CONTAINER.TYPE_, PLACEMENT.ROW_, PLACEMENT.TIER_, NOTA_DELIVERY.NO_NOTA, BLOCKING_AREA.NAME , yard_area.NAMA_YARD,
            container_delivery.via, TO_CHAR(CONTAINER_DELIVERY.TGL_DELIVERY) TGL_END, REQUEST_DELIVERY.NO_RO, CONTAINER_DELIVERY.KETERANGAN
            FROM request_delivery left join nota_delivery on REQUEST_DELIVERY.NO_REQUEST = NOTA_DELIVERY.NO_REQUEST
            inner join container_delivery on CONTAINER_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
            inner join master_container on MASTER_CONTAINER.NO_CONTAINER = CONTAINER_DELIVERY.NO_CONTAINER
            left join placement on PLACEMENT.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
            left join blocking_area on BLOCKING_AREA.ID = PLACEMENT.ID_BLOCKING_AREA
            left join v_mst_pbm emkl on REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
            left join yard_area on blocking_area.ID_YARD_AREA = yard_area.ID
            WHERE REQUEST_DELIVERY.NO_REQUEST = '$no_req'
            --and nota_delivery.TGL_NOTA = (SELECT MAX(e.TGL_NOTA) FROM NOTA_DELIVERY e WHERE e.NO_REQUEST = request_delivery.NO_REQUEST)
            --AND rownum <= 10
            ORDER BY REQUEST_DELIVERY.NO_REQUEST DESC ";
        } else {
            $query_list = "SELECT DISTINCT CONTAINER_DELIVERY.ASAL_CONT,
                                TO_CHAR (container_delivery.START_PERP) START_PERP,
                                TO_CHAR (CONTAINER_DELIVERY.START_STACK) TGL_START,
                                TO_CHAR (REQUEST_DELIVERY.TGL_REQUEST) TGL_REQUEST,
                                request_delivery.STATUS STATUS_REQ,
                                REQUEST_DELIVERY.NO_REQUEST,
                                emkl.NM_PBM AS NAMA_EMKL,
                                CONTAINER_DELIVERY.STATUS,
                                MASTER_CONTAINER.NO_CONTAINER,
                                emkl2.NM_PBM NAMA_PNMT,
                                MASTER_CONTAINER.SIZE_,
                                MASTER_CONTAINER.TYPE_,
                                NOTA_DELIVERY.NO_NOTA,
                                container_delivery.via,
                                TO_CHAR (CONTAINER_DELIVERY.TGL_DELIVERY) TGL_END,
                                container_delivery.BERAT,
                                REQUEST_DELIVERY.NO_RO,
                                CONTAINER_DELIVERY.KETERANGAN,
                                CONTAINER_DELIVERY.EX_BP_ID,
                                '' NAME,
                                '' SLOT_,
                                '' ROW_,
                                '' TIER_
						    FROM request_delivery
                                INNER JOIN nota_delivery
                                    ON REQUEST_DELIVERY.NO_REQUEST = NOTA_DELIVERY.NO_REQUEST
                                INNER JOIN container_delivery
                                    ON CONTAINER_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                INNER JOIN master_container
                                    ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_DELIVERY.NO_CONTAINER
                                LEFT JOIN v_mst_pbm emkl
                                    ON REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                LEFT JOIN v_mst_pbm emkl2
                                    ON REQUEST_DELIVERY.KD_AGEN = emkl2.KD_PBM AND emkl2.KD_CABANG = '05'
						    WHERE REQUEST_DELIVERY.NO_REQUEST = '$no_req'
						        AND nota_delivery.TGL_NOTA =
                                        (SELECT MAX (e.TGL_NOTA)
                                            FROM NOTA_DELIVERY e
                                            WHERE e.NO_REQUEST = request_delivery.NO_REQUEST)";
        }

        $row_list    = DB::connection('uster')->select($query_list);
        foreach ($row_list as $val) {
            $nocont = $val->no_container;
            $ex_bp = $val->ex_bp_id;
            if ($ex_bp == NULL) {
                $sp_id = "SELECT b.no_container, b.NAME, B.SLOT_, B.ROW_, B.TIER_ from (select placement.no_container,blocking_area.NAME, placement.SLOT_, 		placement.ROW_, placement.TIER_ from placement left join blocking_area on BLOCKING_AREA.ID = PLACEMENT.ID_BLOCKING_AREA
			                left join yard_area on blocking_area.ID_YARD_AREA = yard_area.ID order by tgl_update desc) b where  rownum <= 1 and no_container = '$nocont'";
                $r = DB::connection('uster')->selectOne($sp_id);
                $val->name = $r->name;
                $val->slot_ = $r->slot_;
                $val->row_ = $r->row_;
                $val->tier_ = $r->tier_;
            }
        }

        $query_list_ = "UPDATE REQUEST_DELIVERY set CETAK_KARTU = CETAK_KARTU+1 WHERE NO_REQUEST = '$no_req'";
        // DB::connection('uster')->statement($query_list);

        return view('print.sp2.cetak_kartu', ['row_list' => $row_list, 'name' => $name]);
    }
}
