<?php

namespace App\Http\Controllers\Operation\Gate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GateOutController extends Controller
{
    public function index()
    {
        return view('operation.gate.gate-out');
    }


    public function getContainer(Request $request)
    {
        $no_cont = $request->term;
        $query      = "SELECT DISTINCT a.NO_CONTAINER, a.NO_REQUEST, b.SIZE_, A.STATUS , b.TYPE_, a.TGL_DELIVERY TGL_REQUEST_DELIVERY, n.NO_NOTA, n.EMKL nm_pbm
        FROM MASTER_CONTAINER b, CONTAINER_DELIVERY a, REQUEST_DELIVERY d, nota_delivery n
        WHERE a.NO_CONTAINER = b.NO_CONTAINER
        AND a.NO_REQUEST = d.NO_REQUEST
        AND n.no_request = d.no_request 
        AND b.LOCATION = 'IN_YARD'
        AND a.AKTIF = 'Y'
        AND a.NO_CONTAINER LIKE '$no_cont%' AND d.DELIVERY_KE = 'LUAR'
        and N.LUNAS = 'YES' and N.STATUS <> 'BATAL'
        ORDER BY a.NO_REQUEST";

        return DB::connection('uster')->select($query);
    }

    function addGateOut(Request $request)
    {
        $no_cont    = strtoupper($request->NO_CONT);
        $no_req        = $request->NO_REQ;
        $no_truck    = $request->NO_TRUCK;
        $kode_truck    = $request->KD_TRUCK;
        $no_seal    = $request->NO_SEAL;
        $status     = $request->STATUS;
        $masa_berlaku = $request->MASA_BERLAKU;
        $keterangan      = $request->KETERANGAN;
        $id_user    = session("LOGGED_STORAGE");
        $id_yard    = session("IDYARD_STORAGE");


        $selisih    = "SELECT TRUNC(TO_DATE('$masa_berlaku','DD/MM/RR') - SYSDATE) SELISIH FROM dual";
        $row_cek    = DB::connection('uster')->selectOne($selisih);;
        $selisih_tgl    = $row_cek->selisih;

       

        $qcek_gati = "SELECT COUNT(NO_CONTAINER) AS JUM
			  FROM GATE_OUT
			  WHERE NO_CONTAINER = '$no_cont'
			  AND NO_REQUEST = '$no_req'";
        $rwc_gati = DB::connection('uster')->selectOne($qcek_gati);
        $jum_gati = $rwc_gati->jum;
        if ($jum_gati > 0) {
            echo "EXIST_GATO";
            exit();
        }

        //cek request relokasi internal
        $q_cek_relokasi = "SELECT REQUEST_RELOKASI.NO_REQUEST NOREQ, REQUEST_RELOKASI.* FROM REQUEST_RELOKASI, CONTAINER_RELOKASI WHERE REQUEST_RELOKASI.NO_REQUEST = CONTAINER_RELOKASI.NO_REQUEST 
					AND NO_CONTAINER = '$no_cont' AND NO_REQUEST_DELIVERY = '$no_req'";
        $row_cek = DB::connection('uster')->selectOne($q_cek_relokasi);
        $no_req_relokasi = $row_cek->noreq;
        if ($row_cek["TIPE_RELOKASI"] == 'INTERNAL') {
            $q_insert_lolo = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST, ID_YARD)
				 VALUES('$no_cont','DELIVERY','$status',SYSDATE,'LIFT ON','$no_req','$id_yard')";
            $q_insert_lolo_ = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST, ID_YARD)
				 VALUES('$no_cont','DELIVERY','$status',SYSDATE,'LIFT OFF','$no_req','$id_yard')";
            $q_insert_haulage = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST, ID_YARD)
				 VALUES('$no_cont','DELIVERY','$status',SYSDATE,'HAULAGE','$no_req','$id_yard')";

            DB::connection('uster')->insert($q_insert_lolo);
            DB::connection('uster')->insert($q_insert_lolo_);
            DB::connection('uster')->insert($q_insert_haulage);

    

            $query_insert    = "INSERT INTO GATE_OUT( NO_REQUEST, NO_CONTAINER, ID_USER, TGL_IN, NOPOL, STATUS, NO_SEAL, TRUCKING, ID_YARD, KETERANGAN) VALUES('$no_req', '$no_cont', '$id_user', SYSDATE, '$no_truck', '$status','$no_seal','$kode_truck','$id_yard','$keterangan')";
            // echo $query_insert;
            //  $id_user        = $_SESSION["LOGGED_STORAGE"];
            $id_yard    = $_SESSION["IDYARD_STORAGE"];

            $q_getcounter1 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont' ORDER BY COUNTER DESC";
            $rw_getcounter1 = DB::connection('uster')->selectOne($q_getcounter1);
            $cur_booking1  = $rw_getcounter1->no_booking;
            $cur_counter1  = $rw_getcounter1->counter;

            $history        = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, STATUS_CONT, NO_BOOKING, COUNTER) 
													  VALUES ('$no_cont','$no_req','GATE OUT',SYSDATE,'$id_user', '$id_yard','$status','$cur_booking1','$cur_counter1')";

            DB::connection('uster')->insert($history);
            DB::connection('uster')->update("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$no_cont'");
            DB::connection('uster')->update("UPDATE CONTAINER_DELIVERY SET AKTIF = 'T' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
            DB::connection('uster')->update("UPDATE CONTAINER_RELOKASI SET AKTIF = 'T' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req_relokasi'");
            $cek_placement = "SELECT * FROM PLACEMENT INNER JOIN BLOCKING_AREA ON PLACEMENT.ID_BLOCKING_AREA = BLOCKING_AREA.ID WHERE BLOCKING_AREA.ID_YARD_AREA = '$id_yard' AND PLACEMENT.NO_CONTAINER = '$no_cont'";
            $r_b = DB::connection('uster')->selectOne($cek_placement);
            $block_ = $r_b->id_blocking_area;
            DB::connection('uster')->delete("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont'");
            if (DB::connection('uster')->insert($query_insert)) {
                echo "OK";
            }
        } else {
            //echo $selisih_tgl;
            if ($selisih_tgl < 0) {
                echo "EXPIRED";
            } else {

                // echo "bisa gato";die;

                $query_insert    = "INSERT INTO GATE_OUT( NO_REQUEST, NO_CONTAINER, ID_USER, TGL_IN, NOPOL, STATUS, NO_SEAL, TRUCKING, ID_YARD, KETERANGAN) VALUES('$no_req', '$no_cont', '$id_user', SYSDATE, '$no_truck', '$status','$no_seal','$kode_truck','$id_yard','$keterangan')";
                DB::connection('uster')->insert($query_insert);
                
                $id_yard    = $_SESSION["IDYARD_STORAGE"];

             
                $qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE, STATUS_CONT FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'";
                $rwbook = DB::connection('uster')->selectOne($qbook);

                $cur_booking1 = $rwbook->no_booking;
                $cur_counter1 = $rwbook->counter;
                $tgl_update = $rwbook->tgl_update;
                $status_ = $rwbook->status_cont;

                $history        = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, STATUS_CONT, NO_BOOKING, COUNTER) 
														  VALUES ('$no_cont','$no_req','GATE OUT',TO_DATE ('$tgl_update', 'MM/DD/YYYY HH:MI:SS AM'),'$id_user', '$id_yard','$status_','$cur_booking1','$cur_counter1')";

                DB::connection('uster')->insert($history);
                DB::connection('uster')->update("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$no_cont'");
                DB::connection('uster')->update("UPDATE CONTAINER_DELIVERY SET AKTIF = 'T' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                DB::connection('uster')->update("UPDATE CONTAINER_RELOKASI SET AKTIF = 'T' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req_relokasi'");
                $cek_placement = "SELECT * FROM PLACEMENT INNER JOIN BLOCKING_AREA ON PLACEMENT.ID_BLOCKING_AREA = BLOCKING_AREA.ID WHERE BLOCKING_AREA.ID_YARD_AREA = '$id_yard' AND PLACEMENT.NO_CONTAINER = '$no_cont'";
                $r_b = DB::connection('uster')->selectOne($cek_placement);
                $block_ = $r_b->id_blocking_area;
                DB::connection('uster')->delete("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont'");
                echo "OK";
                exit();
            }
        }
    }
}
