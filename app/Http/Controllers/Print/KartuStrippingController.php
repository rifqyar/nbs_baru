<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Print\KartuStripping;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class KartuStrippingController extends Controller
{
    protected $kartu;

    public function __construct(KartuStripping $kartu)
    {
        $this->kartu = $kartu;
    }


    function KartuStripping()
    {
        return view('print.stripping.kartu-spp');
    }

    function GetSSPStripping(Request $request)
    {
        $data = $this->kartu->GetSSPStripping($request);
        return DataTables::of($data)
            ->addColumn('action', function ($listStuffing) {
                $button = '';
                if ($listStuffing->lunas == 'YES') {
                    $cetak = route('uster.print.stripping.Cetak', ['no_req' => $listStuffing->no_request]);
                    $cetakan = route('uster.print.stripping.Cetakan', ['no_req' => $listStuffing->no_request]);
                    $cetak_spk = route('uster.print.stripping.CetakSPK', ['no_req' => $listStuffing->no_request]);

                    $button .= '<div style="margin-bottom:10px">';
                    $button .= '<a href="' . $cetak . '" class="btn btn-primary" target="_blank"><i class="fas fa-file-pdf"></i> Cetak Kartu SPPS (103) </a>';
                    $button .= '</div><div style="margin-bottom:10px">';
                    $button .= '<a href="' . $cetakan . '" class="btn btn-primary" target="_blank"><i class="fas fa-file-pdf"></i> Cetak Kartu SPPS (shrink) </a>';
                    $button .= '</div><div style="margin-bottom:10px">';
                    $button .= '<button class="btn btn-primary" onclick="info_lapangan(\'' . $listStuffing->no_request . '\')"><i class="fas fa-print"></i> Cetak Per Container </button>';
                    $button .= '</div><div style="margin-bottom:10px">';
                    $button .= '<a href="' . $cetak_spk . '" class="btn btn-primary" target="_blank"><i class="fas fa-file-pdf"></i> Cetak SPK</a>';
                    $button .= '</div>';
                } else {
                    $button .= '<span class="badge badge-danger">' . $listStuffing->lunas . ' Nota Belum Lunas</span>';
                }
                return $button;
            })->make(true);
    }

    function KartuTruck()
    {
        return view('print.stripping.kartu-truck');
    }

    function GetTruckStripping(Request $request)
    {
        $data = $this->kartu->GetTruckStripping($request);
        return DataTables::of($data)
            ->addColumn('action', function ($listStuffing) {
                if ($listStuffing->lunas  == 'YES') {
                    $button = '';
                    $button .= '<a style="margin-bottom:10px" href="/print/print_card_praya?no_req=' . $listStuffing->no_request  . '" class="btn btn-success" target="_blank"><i class="fas fa-print"></i> Cetak Semua Kartu Truk </a>';
                    $button .= '<br/>';
                    $button .= '<button class="btn btn-info" onclick="cont_list(\'' . $listStuffing->no_request  . '\')"><i class="fas fa-print"></i> Cetak per Container</button>';
                } else {
                    $button = '<span class="badge badge-secondary">Nota Belum Lunas</span>';
                }
                return $button;
            })->make(true);
    }

    function Cetak(Request $request)
    {
        $name         = session('NAME');
        $no_req     = $request->no_req;

        $query_stat_nota = "SELECT TRIM(LUNAS) AS LUNAS FROM NOTA_STRIPPING WHERE NO_REQUEST='$no_req'";

        $row_stat_nota        = DB::connection('uster')->selectOne($query_stat_nota);
        $lunas_nota        = $row_stat_nota->lunas ?? NULL;


        if ($lunas_nota == 'YES') {

            $query_update = "UPDATE REQUEST_STRIPPING SET CETAK_KARTU_SPPS = CETAK_KARTU_SPPS + 1 WHERE NO_REQUEST = '$no_req'";
            DB::connection('uster')->update($query_update);


            $no_req     = $request->no_req;


            $query_get_container    = "SELECT CONTAINER_STRIPPING.*, MASTER_CONTAINER.SIZE_, TO_CHAR(REQUEST_STRIPPING.TGL_REQUEST+3,'dd/mm/yyyy') TGL_REQUEST , request_stripping.CETAK_KARTU_SPPS
								FROM CONTAINER_STRIPPING
								INNER JOIN REQUEST_STRIPPING ON CONTAINER_STRIPPING.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST
								JOIN MASTER_CONTAINER ON CONTAINER_STRIPPING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
								WHERE CONTAINER_STRIPPING.NO_REQUEST = '$no_req'";
            $row_cont        = DB::connection('uster')->select($query_get_container);;


            foreach ($row_cont as $row) {
                //insert satu satu ke kartu stripping, masing-masing 4 kali
                $no_container    = $row->no_container;
                $tgl_request    = $row->tgl_request;
                $size            = $row->size_;

                //---------------- cek apakah sudah pernah dicetak sebelumnya atau belum

                $query_cek    = "SELECT COUNT(1) AS CEK FROM KARTU_STRIPPING WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_container'";
                $row_cek    = DB::connection('uster')->selectOne($query_cek);

                if ($row_cek->cek > 0) {
                    // sudah pernah di insert
                } else {
                    // belum pernah di insert, insert kartu stripping
                    if ($size == "20")
                        $j = 4;
                    else if ($size == "40")
                        $j = 8;

                    for ($i = 1; $i <= $j; $i++) {
                        $query_insert_kartu    = "INSERT INTO KARTU_STRIPPING(
																	NO_KARTU,
																	NO_REQUEST,
																	NO_CONTAINER,
																	TGL_BERLAKU,
																	AKTIF
																	)
																VALUES(
																	CONCAT('$no_req','-$i'),
																	'$no_req',
																	'$no_container',
																	TO_DATE('$tgl_request','dd-mm-yyyy') + 3,
																	'Y'
																	)
																	";

                        DB::connection('uster')->insert($query_insert_kartu);
                    }
                }
            }


            $query_list = "SELECT g.NM_CONSIGNEE AS EMKL,
              a.NO_REQUEST AS NO_REQUEST,
              c.NO_CONTAINER AS NO_CONTAINER,
			  a.STATUS_REQ, a.CONSIGNEE_PERSONAL, k.ASAL_CONT,
			  a.PERP_KE,
              d.SIZE_ AS SIZE_,
              d.TYPE_ AS TYPE_,
			  d.NO_BOOKING AS NO_BOOKING,
              a.NO_REQUEST_RECEIVING,
              TO_CHAR(c.TGL_APPROVE,'dd/mm/rrrr') TGL_AWAL,
              TO_CHAR(c.TGL_APP_SELESAI,'dd/mm/rrrr') TGL_AKHIR,
			  TO_CHAR(c.START_PERP_PNKN,'dd/mm/rrrr') START_PNKN_,
              TO_CHAR(c.END_STACK_PNKN,'dd/mm/rrrr') END_PNKN_,
			  TO_CHAR(c.TGL_BONGKAR,'dd/mm/rrrr') START_PNKN,
			   CASE WHEN c.TGL_SELESAI  IS NULL
                             THEN TO_CHAR (c.TGL_BONGKAR + 4, 'dd/mm/rrrr')
                             ELSE
                              TO_CHAR (c.TGL_SELESAI, 'dd/mm/rrrr')
                             END AS END_PNKN,
              c.AFTER_STRIP,
			  a.NO_DO,
			  a.NO_BL,
			  a.CETAK_KARTU_SPPS,
			  k.LOKASI_TPK,
              a.o_vessel,
              a.o_voyin,
              a.o_voyout,
              h.PIN_NUMBER
       FROM REQUEST_STRIPPING a
                JOIN CONTAINER_STRIPPING c
                    ON  a.NO_REQUEST = c.NO_REQUEST
                 JOIN MASTER_CONTAINER d
                    ON c.NO_CONTAINER = d.NO_CONTAINER
				 LEFT JOIN PLAN_CONTAINER_STRIPPING k
					ON d.NO_CONTAINER = k.NO_CONTAINER AND c.NO_REQUEST = REPLACE(K.NO_REQUEST,'P','S')
                LEFT JOIN REQUEST_RECEIVING g
                   ON a.NO_REQUEST_RECEIVING = g.NO_REQUEST
                LEFT JOIN BILLING.REQ_DELIVERY_D h
                	ON a.O_REQNBS = trim(h.ID_REQ) AND c.NO_CONTAINER = h.NO_CONTAINER
                WHERE a.NO_REQUEST = '$no_req' ";
            if (isset($_GET['no_cont'])) {
                $no_cont = $_GET['no_cont'];
                $query_list .= "AND c.NO_CONTAINER = '$no_cont'";
            }


            $row_list    = DB::connection('uster')->select($query_list);

            $data = $this->kartu->Cetak($request);
            return view('print.stripping.print.cetak', compact('name', 'row_list'));
        } else {
            echo "NOTA BELUM LUNAS";
        }
    }

    function CetakSPK(Request $request)
    {
        $no_req     = $request->no_req;

        $query_update = "UPDATE REQUEST_STRIPPING SET CETAK_KARTU_SPPS = CETAK_KARTU_SPPS + 1";
        DB::connection('uster')->statement($query_update);



        $query_get_container    = "SELECT CONTAINER_STRIPPING.*, MASTER_CONTAINER.SIZE_, TO_CHAR(REQUEST_STRIPPING.TGL_REQUEST+3,'dd/mm/yyyy') TGL_REQUEST
								FROM CONTAINER_STRIPPING
								INNER JOIN REQUEST_STRIPPING ON CONTAINER_STRIPPING.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST
								JOIN MASTER_CONTAINER ON CONTAINER_STRIPPING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
								WHERE CONTAINER_STRIPPING.NO_REQUEST = '$no_req'";
        $row_cont        =  DB::connection('uster')->select($query_get_container);


        $query_list = "SELECT b.NM_PBM AS EMKL,
			   b.ALMT_PBM AS ALAMAT,
              b.NO_NPWP_PBM AS NPWP,
              a.NO_REQUEST AS NO_REQUEST, a.STATUS_REQ,
              c.NO_CONTAINER AS NO_CONTAINER,
              d.SIZE_ AS SIZE_,
              d.TYPE_ AS TYPE_,
              a.NO_REQUEST_RECEIVING,
			  TO_CHAR(c.TGL_APPROVE,'dd/mm/rrrr') TGL_AWAL,
              TO_DATE(c.TGL_APPROVE)+2 TGL_AKHIR,
			  TO_CHAR(SYSDATE,'dd/mm/rrrr') SYSDATE_
       FROM REQUEST_STRIPPING a
                INNER JOIN v_mst_pbm b
                    ON a.KD_CONSIGNEE = b.KD_PBM
                JOIN CONTAINER_STRIPPING c
                    ON  a.NO_REQUEST = c.NO_REQUEST
                 JOIN MASTER_CONTAINER d
                    ON c.NO_CONTAINER = d.NO_CONTAINER
                WHERE a.NO_REQUEST = '$no_req'";


        $row_list    =  DB::connection('uster')->select($query_list);

        $rowspv = "SELECT NAMA_PEGAWAI, JABATAN FROM MASTER_PEGAWAI WHERE STATUS = 'SPK'";
        $rowspv =  DB::connection('uster')->selectOne($rowspv);


        return view('print.stripping.print.cetak_spk', compact('rowspv', 'row_list'));
    }

    function Cetakan(Request $request)
    {
        $name         = session('NAME');
        $no_req     = $request->no_req;

        $query_update = "UPDATE REQUEST_STRIPPING SET CETAK_KARTU_SPPS = CETAK_KARTU_SPPS + 1 WHERE NO_REQUEST = '$no_req'";
        DB::connection('uster')->statement($query_update);


        $no_req     = $request->no_req;


        $query_list = "SELECT b.NM_PBM AS EMKL,
                  a.NO_REQUEST AS NO_REQUEST,
                  c.NO_CONTAINER AS NO_CONTAINER,
                  a.STATUS_REQ, a.CONSIGNEE_PERSONAL, k.ASAL_CONT,
                  a.PERP_KE,
                  d.SIZE_ AS SIZE_,
                  d.TYPE_ AS TYPE_,
                  d.NO_BOOKING AS NO_BOOKING,
                  a.NO_REQUEST_RECEIVING,
                  TO_CHAR(c.TGL_APPROVE,'dd/mm/rrrr') TGL_AWAL,
                  TO_CHAR(c.TGL_APP_SELESAI,'dd/mm/rrrr') TGL_AKHIR,
                  TO_CHAR(c.START_PERP_PNKN,'dd/mm/rrrr') START_PNKN_,
                  TO_CHAR(c.END_STACK_PNKN,'dd/mm/rrrr') END_PNKN_,
                  TO_CHAR(c.TGL_BONGKAR,'dd/mm/rrrr') START_PNKN,
                   CASE WHEN c.TGL_SELESAI  IS NULL
                                 THEN TO_CHAR (c.TGL_BONGKAR + 4, 'dd/mm/rrrr')
                                 ELSE
                                  TO_CHAR (c.TGL_SELESAI, 'dd/mm/rrrr')
                                 END AS END_PNKN,
                  c.AFTER_STRIP,
                  a.NO_DO,
                  a.NO_BL,
                  a.CETAK_KARTU_SPPS,
                  k.LOKASI_TPK,
                  a.o_vessel,
                  a.o_voyin,
                  a.o_voyout,
                  h.PIN_NUMBER
           FROM REQUEST_STRIPPING a
                    INNER JOIN V_MST_PBM b
                        ON a.KD_CONSIGNEE = b.KD_PBM
                    JOIN CONTAINER_STRIPPING c
                        ON  a.NO_REQUEST = c.NO_REQUEST
                     JOIN MASTER_CONTAINER d
                        ON c.NO_CONTAINER = d.NO_CONTAINER
                     LEFT JOIN PLAN_CONTAINER_STRIPPING k
                        ON d.NO_CONTAINER = k.NO_CONTAINER AND c.NO_REQUEST = REPLACE(K.NO_REQUEST,'P','S')
                    LEFT JOIN BILLING.REQ_DELIVERY_D h
                        ON a.O_REQNBS = trim(h.ID_REQ) AND c.NO_CONTAINER = h.NO_CONTAINER
                    WHERE a.NO_REQUEST = '$no_req' ";
        if (isset($_GET['no_cont'])) {
            $no_cont = $_GET['no_cont'];
            $query_list .= "AND c.NO_CONTAINER = '$no_cont'";
        }


        $row_list    = DB::connection('uster')->select($query_list);


        $data = $this->kartu->Cetakan($request);
        return view('print.stripping.print.print', compact('name', 'row_list'));
    }

    function ShowContainer(Request $request)
    {
        $no_req = $request->no_req;
        $get_container = "select no_container from container_stripping where no_request = '$no_req' and no_container is not null";
        $rw_cont = DB::connection('uster')->select($get_container);
        return view('print.stripping.print.modal', compact('no_req', 'rw_cont'));
    }
}
