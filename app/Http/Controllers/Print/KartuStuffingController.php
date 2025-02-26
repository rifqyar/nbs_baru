<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Print\KartuStuffing;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class KartuStuffingController extends Controller
{

    protected $kartu;

    public function __construct(KartuStuffing $kartu)
    {
        $this->kartu = $kartu;
    }

    function KartuStuffing()
    {
        return view('print.stuffing.kartu-spp');
    }

    function GetSSPStuffing(Request $request)
    {
        $data = $this->kartu->GetSSPStuffing($request);
        return DataTables::of($data)
            ->addColumn('action', function ($listStuffing) {
                if ($listStuffing->lunas == 'YES') {
                    $cetak = route('uster.print.stuffing.Cetak', ['no_req' => $listStuffing->no_request]);
                    $cetakan = route('uster.print.stuffing.Cetakan', ['no_req' => $listStuffing->no_request]);
                    $cetak_spk = route('uster.print.stuffing.CetakSPK', ['no_req' => $listStuffing->no_request]);

                    $button = '';
                    $button .= '<div>';
                    $button .= '<a href="' . $cetak . '" class="btn btn-sm btn-primary mb-2" target="_blank"><i class="fas fa-print"></i> Cetak Kartu SPPS (103) </a>';
                    $button .= '</div>';
                    $button .= '<div>';
                    $button .= '<a href="' . $cetakan . '" class="btn btn-sm btn-primary mb-2" target="_blank"><i class="fas fa-print"></i> Cetak Kartu SPPS (shrink) </a>';
                    $button .= '</div>';
                    $button .= '<div>';
                    $button .= '<button class="btn btn-sm btn-primary mb-2" onclick="info_lapangan(\'' . $listStuffing->no_request . '\')"><i class="fas fa-print"></i> Cetak Per Container </button>';
                    $button .= '</div>';
                    $button .= '<div>';
                    $button .= '<a href="' .  $cetak_spk . '" class="btn btn-sm btn-primary" target="_blank"><i class="fas fa-print"></i> Cetak SPK </a>';
                    $button .= '</div>';
                } else {
                    $button = 'Nota Blm Lunas';
                }

                return $button;
            })->make(true);
    }




    function KartuTruck()
    {
        return view('print.stuffing.kartu-truck');
    }

    function GetTruckStuffing(Request $request)
    {
        $data = $this->kartu->GetTruckStuffing($request);
        return DataTables::of($data)
            ->addColumn('action', function ($listStuffing) {
                if ($listStuffing->lunas == 'YES') {
                    $button = '';
                    $button .= '<div>';
                    $button .= '<a href="/print/print_card_praya?no_req=' . $listStuffing->no_request . '" class="btn btn-primary mb-2" target="_blank"><i class="fas fa-print"></i> Cetak Semua Kartu Truk </a>';
                    $button .= '</div>';
                    $button .= '<div>';
                    $button .= '<button class="btn btn-primary" onclick="cont_list(\'' . $listStuffing->no_request . '\')"><i class="fas fa-print"></i> Cetak per Container </button>';
                    $button .= '</div>';
                } else {
                    $button = 'Nota Blm Lunas';
                }

                return $button;
            })->make(true);
    }

    function Cetak(Request $request)
    {
        $no_req     = $request->no_req;

        $query_update = "UPDATE REQUEST_STUFFING SET CETAK_KARTU_SPPS = CETAK_KARTU_SPPS + 1 WHERE NO_REQUEST = '$no_req'";
        DB::connection('uster')->update($query_update);




        $query_get_container    = "SELECT container_stuffing.*, MASTER_CONTAINER.SIZE_, TO_CHAR(request_stuffing.TGL_REQUEST+3,'dd/mm/yyyy') TGL_REQUEST
                                    FROM container_stuffing
                                    INNER JOIN request_stuffing ON container_stuffing.NO_REQUEST = request_stuffing.NO_REQUEST
                                    JOIN MASTER_CONTAINER ON container_stuffing.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                                    WHERE container_stuffing.NO_REQUEST = '$no_req'";
        $row_cont        = DB::connection('uster')->select($query_get_container);


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
                    $query_insert_kartu    = "INSERT INTO KARTU_STUFFING(
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


        $name         = session('NAME');


        //cek apakah perpanjangan atau bukan, karena berbeda ambil tanggal awal penumpukkannya
        $query_cek_perp = "SELECT PERP_KE
                        FROM REQUEST_STUFFING
                        WHERE NO_REQUEST='$no_req'";

        $row_cek_perp        = DB::connection('uster')->selectOne($query_cek_perp);
        $cek_perp            = $row_cek_perp->perp_ke ?? NULL;


        if ($cek_perp == NULL) {
            $query_list = "SELECT b.NM_PBM AS EMKL,
                          a.NO_REQUEST AS NO_REQUEST,
                          c.NO_CONTAINER AS NO_CONTAINER,
                          d.SIZE_ AS SIZE_,
                          d.TYPE_ AS TYPE_,
                          a.NO_REQUEST_RECEIVING,
                          a.NM_KAPAL, a.VOYAGE,
                          i.BLOK_TPK,
                          i.SLOT_TPK,
                          i.ROW_TPK,
                          i.TIER_TPK,
                          c.TYPE_STUFFING,
                          CASE WHEN REMARK_SP2 = 'Y' THEN
                            TO_DATE(c.START_PERP_PNKN,'dd/mm/rrrr')
                          ELSE c.START_STACK END TGL_AWAL,

                          CASE WHEN REMARK_SP2 = 'Y' THEN
                                TO_DATE(c.END_STACK_PNKN,'dd/mm/rrrr')
                          ELSE TO_DATE(c.START_PERP_PNKN,'dd/mm/rrrr') END TGL_AKHIR

                   FROM REQUEST_STUFFING a
                            INNER JOIN V_MST_PBM b
                                ON a.KD_CONSIGNEE = b.KD_PBM
                            JOIN CONTAINER_STUFFING c
                                ON  a.NO_REQUEST = c.NO_REQUEST

                             JOIN MASTER_CONTAINER d
                                ON c.NO_CONTAINER = d.NO_CONTAINER
                                LEFT JOIN CONTAINER_RECEIVING i
                                    ON c.NO_CONTAINER = i.NO_CONTAINER
                                    AND i.NO_REQUEST = a.NO_REQUEST_RECEIVING
                            WHERE a.NO_REQUEST = '$no_req' ";
        } else {
            $query_list = "SELECT b.NM_PBM AS EMKL,
                          a.NO_REQUEST AS NO_REQUEST,
                          c.NO_CONTAINER AS NO_CONTAINER,

                          d.SIZE_ AS SIZE_,
                          d.TYPE_ AS TYPE_,
                          a.NO_REQUEST_RECEIVING,
                          a.NM_KAPAL, a.VOYAGE,
                          a.STATUS_REQ,
                          i.BLOK_TPK,
                          i.SLOT_TPK,
                          i.ROW_TPK,
                          i.TIER_TPK,
                          c.TYPE_STUFFING,
                          c.START_PERP_PNKN TGL_AWAL,
                          c.END_STACK_PNKN TGL_AKHIR

                   FROM REQUEST_STUFFING a
                            INNER JOIN V_MST_PBM b
                                ON a.KD_CONSIGNEE = b.KD_PBM
                            JOIN CONTAINER_STUFFING c
                                ON  a.NO_REQUEST = c.NO_REQUEST

                             JOIN MASTER_CONTAINER d
                                ON c.NO_CONTAINER = d.NO_CONTAINER
                                LEFT JOIN CONTAINER_RECEIVING i
                                    ON c.NO_CONTAINER = i.NO_CONTAINER
                                    AND i.NO_REQUEST = a.NO_REQUEST_RECEIVING
                            WHERE a.NO_REQUEST = '$no_req' ";
        }


        if (isset($_GET['no_cont'])) {
            $no_cont = $_GET['no_cont'];
            $query_list .= "AND c.NO_CONTAINER = '$no_cont'";
        }

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'dd/mm/rrrr'");
        $row_list    = DB::connection('uster')->select($query_list);


        return view('print.stuffing.print.cetak', compact('name', 'row_list'));
    }

    function CetakSPK(Request $request)
    {
        $no_req     = $request->no_req;


        $query_update = "UPDATE REQUEST_STUFFING SET CETAK_KARTU_SPPS = CETAK_KARTU_SPPS + 1";
        DB::connection('uster')->update($query_update);

        $query_list = "SELECT b.NM_PBM AS EMKL,
                   b.ALMT_PBM AS ALAMAT,
                  b.NO_NPWP_PBM AS NPWP,
                  a.NO_REQUEST AS NO_REQUEST,
                  c.NO_CONTAINER AS NO_CONTAINER,
                  d.SIZE_ AS SIZE_,
                  d.TYPE_ AS TYPE_,
                  a.NO_REQUEST_RECEIVING,
                  TO_DATE(c.TGL_APPROVE)+1 TGL_AWAL,
                  TO_DATE(c.TGL_APPROVE)+5 TGL_AKHIR,
                  TO_DATE(SYSDATE) SYSDATE_
           FROM REQUEST_STUFFING a
                    INNER JOIN v_mst_pbm b
                        ON a.KD_CONSIGNEE = b.KD_PBM
                        AND b.KD_CABANG = '05'
                    JOIN CONTAINER_STUFFING c
                        ON  a.NO_REQUEST = c.NO_REQUEST
                     JOIN MASTER_CONTAINER d
                        ON c.NO_CONTAINER = d.NO_CONTAINER
                    --JOIN PLACEMENT g
                    --   ON a.NO_REQUEST_RECEIVING = g.NO_REQUEST_RECEIVING
                    WHERE a.NO_REQUEST = '$no_req' ";


        $row_list    = DB::connection('uster')->select($query_list);


        $qspv = "SELECT NAMA_PEGAWAI, JABATAN FROM MASTER_PEGAWAI WHERE STATUS = 'SPK'";
        $rowspv = DB::connection('uster')->selectOne($qspv);



        return view('print.stuffing.print.cetak_spk', compact('rowspv', 'row_list'));
    }

    function Cetakan(Request $request)
    {
        $name         = session('NAME');
        $no_req     = $request->no_req;

        $query_update = "UPDATE REQUEST_STUFFING SET CETAK_KARTU_SPPS = CETAK_KARTU_SPPS + 1 WHERE NO_REQUEST = '$no_req'";
        DB::connection('uster')->update($query_update);



        $query_get_container    = "SELECT container_stuffing.*, MASTER_CONTAINER.SIZE_, TO_CHAR(request_stuffing.TGL_REQUEST+3,'dd/mm/yyyy') TGL_REQUEST
                                    FROM container_stuffing
                                    INNER JOIN request_stuffing ON container_stuffing.NO_REQUEST = request_stuffing.NO_REQUEST
                                    JOIN MASTER_CONTAINER ON container_stuffing.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                                    WHERE container_stuffing.NO_REQUEST = '$no_req'";
        $row_cont        = DB::connection('uster')->select($query_get_container);


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
                    $query_insert_kartu    = "INSERT INTO KARTU_STUFFING(
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


        $name         = session('NAME');


        //cek apakah perpanjangan atau bukan, karena berbeda ambil tanggal awal penumpukkannya
        $query_cek_perp = "SELECT PERP_KE
                        FROM REQUEST_STUFFING
                        WHERE NO_REQUEST='$no_req'";

        $row_cek_perp        = DB::connection('uster')->selectOne($query_cek_perp);
        $cek_perp            = $row_cek_perp->perp_ke ?? NULL;


        if ($cek_perp == '') {
            $query_list = "SELECT b.NM_PBM AS EMKL,
                          a.NO_REQUEST AS NO_REQUEST,
                          c.NO_CONTAINER AS NO_CONTAINER,
                          d.SIZE_ AS SIZE_,
                          d.TYPE_ AS TYPE_,
                          a.NO_REQUEST_RECEIVING,
                          a.NM_KAPAL, a.VOYAGE,
                          i.BLOK_TPK,
                          i.SLOT_TPK,
                          i.ROW_TPK,
                          i.TIER_TPK,
                          c.TYPE_STUFFING,
                          CASE WHEN REMARK_SP2 = 'Y' THEN
                            TO_DATE(c.START_PERP_PNKN,'dd/mm/rrrr')
                          ELSE c.START_STACK END TGL_AWAL,

                          CASE WHEN REMARK_SP2 = 'Y' THEN
                                TO_DATE(c.END_STACK_PNKN,'dd/mm/rrrr')
                          ELSE TO_DATE(c.START_PERP_PNKN,'dd/mm/rrrr') END TGL_AKHIR
                   FROM REQUEST_STUFFING a
                            INNER JOIN V_MST_PBM b
                                ON a.KD_CONSIGNEE = b.KD_PBM
                            JOIN CONTAINER_STUFFING c
                                ON  a.NO_REQUEST = c.NO_REQUEST
                             JOIN MASTER_CONTAINER d
                                ON c.NO_CONTAINER = d.NO_CONTAINER
                                LEFT JOIN CONTAINER_RECEIVING i
                                    ON c.NO_CONTAINER = i.NO_CONTAINER
                                    AND i.NO_REQUEST = a.NO_REQUEST_RECEIVING
                            WHERE a.NO_REQUEST = '$no_req' ";
        } else {
            $query_list = "SELECT b.NM_PBM AS EMKL,
                          a.NO_REQUEST AS NO_REQUEST,
                          c.NO_CONTAINER AS NO_CONTAINER,
                          d.SIZE_ AS SIZE_,
                          d.TYPE_ AS TYPE_,
                          a.NO_REQUEST_RECEIVING,
                          a.NM_KAPAL, a.VOYAGE,
                          a.STATUS_REQ,
                          i.BLOK_TPK,
                          i.SLOT_TPK,
                          i.ROW_TPK,
                          i.TIER_TPK,
                          c.TYPE_STUFFING,
                          c.START_PERP_PNKN TGL_AWAL,
                          c.END_STACK_PNKN TGL_AKHIR

                   FROM REQUEST_STUFFING a
                            INNER JOIN KAPAL_CABANG.MST_PBM b
                                ON a.KD_CONSIGNEE = b.KD_PBM
                                AND b.KD_CABANG = '05'
                            JOIN CONTAINER_STUFFING c
                                ON  a.NO_REQUEST = c.NO_REQUEST

                             JOIN MASTER_CONTAINER d
                                ON c.NO_CONTAINER = d.NO_CONTAINER
                                LEFT JOIN CONTAINER_RECEIVING i
                                    ON c.NO_CONTAINER = i.NO_CONTAINER
                                    AND i.NO_REQUEST = a.NO_REQUEST_RECEIVING
                            WHERE a.NO_REQUEST = '$no_req' ";
        }


        if (isset($_GET['no_cont'])) {
            $no_cont = $_GET['no_cont'];
            $query_list .= "AND c.NO_CONTAINER = '$no_cont'";
        }

        $row_list    = DB::connection('uster')->selectOne($query_list);;

        return view('print.stuffing.print.print', compact('name', 'row_list'));
    }

    function ShowContainer(Request $request)
    {
        $no_req = $request->no_req;
        $get_container = "select no_container from container_stuffing where no_request = '$no_req' ";
        $rw_cont = DB::connection('uster')->select($get_container);
        return view('print.stuffing.print.modal', compact('no_req', 'rw_cont'));
    }
}
