<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\SpssPerKapalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SpssPerKapalController extends Controller
{
   protected $report;

   public function __construct(SpssPerKapalService $report)
   {
      $this->report = $report;
   }

   function index()
   {
      return view('report.sppsperkapal.spssperkapal');
   }

   function dataTables(Request $request): JsonResponse
   {

      $listDelivery = $this->report->spssDataTable($request);
      return DataTables::of($listDelivery)->make(true);
   }

   function masterVessel(Request $request)
   {
      $viewData = $this->report->masterVessel($request->term);
      return response()->json($viewData);
   }

   function generateExcel(Request $request)
   {

      // echo 'asd';die();
      $jenis        = $request->keg;
      $no_ukk       = $request->ukk;
      $kegiatan     = $request->keg;
      $no_booking   = $request->no_booking;

      $query1            = "SELECT pkk.voyage_in, pkk.nm_kapal, pkk.no_ukk, pkk.no_booking from v_pkk_cont pkk
					where pkk.no_ukk like '$no_ukk%'";
      $row        = DB::connection('uster')->selectOne($query1);
      $nm_kapal     = $row->nm_kapal;
      $voyage     = $row->voyage_in;

      if ($kegiatan == "stripping") {
         $bp_id = "select no_booking from v_pkk_cont where no_ukk = '$no_ukk' and no_booking like 'BP%'";
         $rwbp   = DB::connection('uster')->selectOne($bp_id);
         $bp_id_ = $rwbp->no_booking;

         $query = "SELECT * FROM (  SELECT b.tgl_approve,
                             a.no_container,
                             a.size_,
                             a.type_,
                             CASE WHEN tgL_realisasi IS NULL THEN 'FCL' ELSE 'MTY' END
                                status_,
                             TO_CHAR (c.tgl_request, 'dd/mm/rrrr') tgl_request,
                             c.no_request,
                             b.commodity,
                             d.emkl nm_pbm,
                             b.hz,
                             c.id_yard,
                             f.nama_lengkap nm_request,
                             c.no_request_receiving,
                             b.tgl_realisasi,
                             c.perp_ke,
                             hc.no_booking,
                             tgl_bongkar tgl_awal, 
                            case when c.status_req = 'PERP'
                            then b.end_stack_pnkn
                            else case when b.tgl_selesai is null
                                    then b.tgl_bongkar+4 
                                    else b.tgl_selesai 
                                    end
                            end as tgl_akhir,
                            d.lunas,
                            g.nama_lengkap nm_realisasi,
                            d.status VIA,
                            case when b.pemakaian_alat = 1 then 'Y' else 'N'
                            end pemakaian_alat,
                            bg.tgl_in tgl_gate
                        FROM master_container a
                             JOIN container_stripping b
                                ON a.no_container = b.no_container
                             JOIN history_container hc
                                ON b.no_container = hc.no_container
                                   AND hc.no_request = b.no_request
                                   AND hc.kegiatan IN
                                          ('REQUEST STRIPPING',
                                           'PERPANJANGAN STRIPPING')
                             JOIN request_stripping c
                                ON b.no_request = c.no_request
                             JOIN nota_stripping d
                                ON c.no_request = d.no_request
                             LEFT JOIN master_user f
                                ON c.id_user = f.id
                             LEFT JOIN master_user g
                                ON b.id_user_realisasi = g.id
                             LEFT JOIN border_gate_in bg
                                ON bg.no_request = c.no_request_receiving AND bg.no_container = b.no_container
                       WHERE     d.status <> 'BATAL'
                             AND (b.status_req IS NULL OR b.status_req = 'PERP')
                             AND d.LUNAS = 'YES'
                    ORDER BY no_container) a, (SELECT MAX (perp_ke) max_perp, history_container.no_booking booking, container_stripping.no_container container_
                                FROM request_stripping, container_stripping, history_container
                               WHERE container_stripping.no_request =
                                        request_stripping.no_request                             
                                     AND history_container.no_container = container_stripping.no_container   
                                     and history_container.no_request = container_stripping.no_Request
                                     GROUP BY history_container.no_booking, container_stripping.no_container) z
                   WHERE  a.no_container = z.container_(+) and a.no_booking=z.booking(+) and a.no_booking = '$bp_id_'
                   and a.perp_ke = z.max_perp";
      } else {
         $bp_id = "select no_booking from v_pkk_cont where no_ukk = '$no_ukk' and no_booking not like 'BP%'";
         $rwbp   = DB::connection('uster')->selectOne($bp_id);
         $bp_id_ = $rwbp->no_booking;
         $query = "SELECT a.no_container,
                   a.HIDE,
                   a.size_,
                   a.no_request,
                   a.tgl_request,
                   a.nama_lengkap nm_request,
                   a.emkl nm_pbm,
                   a.tgl_awal,
                   a.tgl_akhir,
                   a.lunas,
                   a.hz,
                   a.nama_yard,           
                   a.commodity,
                   a.asal_cont,
                   a.berat,
                   a.tgl_realisasi,
                   a.nm_realisasi,
                   rd.tgl_request tgl_req_delivery,
                   RD.NO_REQUEST no_request_delivery,
                   rd.vessel,
                   rd.voyage, 
                   bd.tgl_in tgl_gate,
                      a.no_booking,
                      case when a.type_stuffing = 'STUFFING_LAP' then 'Lapangan'
                       when a.type_stuffing = 'STUFFING_GUD_TONGKANG' then 'Gudang Tongkang'
                       when a.type_stuffing = 'STUFFING_GUD_TRUCK' then 'Gudang Truk'
                  else a.type_stuffing end as STATUS,
                  pemakaian_alat
              FROM (SELECT a.no_container,
                            CASE 
                                WHEN b.aktif = 'T' and b.tgl_realisasi IS NULL THEN 'NO'
                                ELSE 'YES'
                           END HIDE,
                           a.size_,
                           a.type_,
                           CASE
                              WHEN b.tgl_realisasi IS NULL THEN 'MTY'
                              ELSE 'FCL'
                           END
                              status,
                           TO_CHAR (c.tgl_request, 'dd/mm/rrrr hh24:mi:ss') tgl_request,
                           CASE 
                                WHEN c.status_req = 'PERP' THEN b.start_perp_pnkn
                                ELSE b.start_stack
                           END tgl_awal,
                           CASE 
                                WHEN c.status_req = 'PERP' THEN b.end_stack_pnkn
                                ELSE b.start_perp_pnkn
                           END tgl_akhir,
                           c.no_request,
                           v.nm_pbm emkl,
                           case when c.stuffing_dari = 'AUTO' then 'YES'
                           else d.lunas
                           end LUNAS,
                           b.hz,
                           e.nama_yard,
                           f.nama_lengkap,
                           j.nama_lengkap nm_realisasi,
                           c.no_request_receiving,
                           b.tgl_realisasi,
                           b.commodity,
                           b.berat,
                           b.type_stuffing,
                           b.asal_cont,
                           c.no_booking,
                           b.f_batal_muat,
                           case when B.pemakaian_alat = '1' then 'Y' else 'N'
                            end pemakaian_alat
                      FROM master_container a
                           JOIN container_stuffing b
                              ON a.no_container = b.no_container
                           JOIN request_stuffing c
                              ON b.no_request = c.no_request
                           LEFT JOIN v_mst_pbm v
                              ON c.kd_consignee = v.kd_pbm AND v.kd_cabang = '05'
                           LEFT JOIN nota_stuffing d
                              ON c.no_request = d.no_request AND d.status <> 'BATAL' AND D.LUNAS = 'YES'
                           LEFT JOIN yard_area e
                              ON c.id_yard = e.id
                           LEFT JOIN master_user f
                              ON c.id_user = f.id
                           LEFT JOIN master_user j
                              ON b.id_user_realisasi = j.id
                     WHERE  (B.STATUS_REQ = 'PERP' OR B.STATUS_REQ IS NULL) AND b.f_batal_muat IS NULL) a
                   LEFT JOIN container_delivery cd
                      ON cd.noreq_peralihan = a.no_request
                         AND cd.no_container = a.no_container
                   LEFT JOIN request_delivery rd
                      ON rd.no_request = cd.no_request
                   LEFT JOIN border_gate_out bd
                      ON cd.no_container = bd.no_container
                         AND bd.no_request = cd.no_request
                   WHERE a.HIDE = 'YES' and a.no_booking = '$bp_id_'";

         $new_query = "SELECT a.*, b.no_req_baru NO_REQUEST_DELIVERY FROM 
                              (SELECT master_container.no_container,
                                     master_container.size_,
                                     container_stuffing.no_request,
                                     container_delivery.no_request req_del,
                                     request_stuffing.tgl_request,
                                     container_stuffing.commodity,
                                     master_user.nama_lengkap NM_REQUEST,
                                     nota_stuffing.emkl NM_PBM,
                                     CASE 
                                          WHEN request_stuffing.status_req = 'PERP' THEN container_stuffing.start_perp_pnkn
                                          ELSE container_stuffing.start_stack
                                     END tgl_awal,
                                     CASE 
                                          WHEN request_stuffing.status_req = 'PERP' THEN container_stuffing.end_stack_pnkn
                                          ELSE container_stuffing.start_perp_pnkn
                                     END tgl_akhir,
                                     nota_stuffing.lunas,
                                     'X' tgl_gate,
                                     container_stuffing.tgl_realisasi,
                                     m1.nama_lengkap as nm_realisasi,
                                     case when container_stuffing.type_stuffing = 'STUFFING_LAP' then 'Lapangan'
                                             when container_stuffing.type_stuffing = 'STUFFING_GUD_TONGKANG' then 'Gudang Tongkang'
                                             when container_stuffing.type_stuffing = 'STUFFING_GUD_TRUCK' then 'Gudang Truk'
                                        else container_stuffing.type_stuffing end as STATUS
                                FROM request_stuffing,
                                     container_stuffing,
                                     nota_stuffing,
                                     master_container,
                                     container_delivery,
                                     master_user,
                                     master_user m1
                               WHERE     request_stuffing.no_request = container_stuffing.no_request
                                     AND request_stuffing.no_request = nota_stuffing.no_request
                                     AND container_stuffing.no_container = master_container.no_container
                                     AND container_delivery.noreq_peralihan = container_stuffing.no_request
                                     AND container_delivery.no_container = container_stuffing.no_container
                                     AND master_user.id = request_stuffing.id_user
                                     AND m1.id = container_stuffing.id_user_realisasi) a,
                              (SELECT container_batal_muat.no_req_batal, container_batal_muat.no_container, request_batal_muat.no_req_baru
                                FROM  request_batal_muat
                                     JOIN container_batal_muat
                                        ON request_batal_muat.no_request = container_batal_muat.no_request
                               WHERE kapal_tuju = '$bp_id_' and status_gate = 2) b
                               WHERE a.no_container = b.no_container AND a.req_del = b.no_req_batal";
      }
      $row_q = DB::connection('uster')->select($query);

      if ($kegiatan == "stuffing") {
         $n_query = DB::connection('uster')->select($new_query);
         if (count($n_query) > 0) {
            foreach ($n_query as $key) {
               if ($key->tgl_gate == 'X') {

                  $req_dl     = $key->no_request_delivery;
                  $g_tgl_gate = "SELECT TGL_IN FROM BORDER_GATE_OUT WHERE NO_REQUEST = '$req_dl'";
                  $r_tgl_gate = DB::connection('uster')->selectOne($g_tgl_gate);
                  $key->tgl_gate = $r_tgl_gate->tgl_in;
               }
            }
            $n_query[0] = &$key;

            $all_res = array_merge($row_q, $n_query);
            $row_q = &$all_res;
         }
      }

      $tanggal = date("dmY");
      $data = [
         'nm_kapal' => $nm_kapal,
         'voyage' => $voyage,
         'kegiatan' => $kegiatan,
         'row_q' => $row_q,
         'jenis' => $jenis,
         'tanggal' => $tanggal
      ];


      return view('report.sppsperkapal.toexcel.toexcel', $data);
   }
}
