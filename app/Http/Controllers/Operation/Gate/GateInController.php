<?php

namespace App\Http\Controllers\Operation\Gate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GateInController extends Controller
{
    public function index()
    {
        return view('operation.gate.gate-in');
    }

    public function getContainer(Request $request)
    {
        $req = $request->term;
        $query      = "SELECT CONTAINER_RECEIVING.NO_REQUEST AS NO_REQUEST,
               CONTAINER_RECEIVING.STATUS AS STATUS,
               MASTER_CONTAINER.NO_CONTAINER AS NO_CONTAINER,
               MASTER_CONTAINER.SIZE_ AS SIZE_,
               MASTER_CONTAINER.TYPE_ AS TYPE_,
               NOTA_RECEIVING.EMKL AS EMKL,
               NVL (NOTA_RECEIVING.NO_FAKTUR, '') AS NO_NOTA,
               REQUEST_RECEIVING.RECEIVING_DARI,
               REQUEST_RECEIVING.PERALIHAN
          FROM MASTER_CONTAINER
               JOIN CONTAINER_RECEIVING
                  ON MASTER_CONTAINER.NO_CONTAINER =
                        CONTAINER_RECEIVING.NO_CONTAINER
               JOIN REQUEST_RECEIVING
                  ON CONTAINER_RECEIVING.NO_REQUEST =
                        REQUEST_RECEIVING.NO_REQUEST AND REQUEST_RECEIVING.RECEIVING_DARI = 'LUAR'
               JOIN NOTA_RECEIVING
                  ON REQUEST_RECEIVING.NO_REQUEST = NOTA_RECEIVING.NO_REQUEST
         WHERE    CONTAINER_RECEIVING.NO_CONTAINER = trim('$req')
                AND MASTER_CONTAINER.LOCATION = 'GATO'
               AND NOTA_RECEIVING.STATUS IN ('NEW','KOREKSI')
               AND NOTA_RECEIVING.LUNAS = 'YES'
               AND CONTAINER_RECEIVING.AKTIF = 'Y'";

        return DB::connection('uster')->select($query);
    }

    function addGateIn(Request $request)
    {
        $no_cont    = strtoupper($request->NO_CONT);
        $no_req        = $request->NO_REQ;
        $no_pol        = $request->NO_POL;
        $status        = $request->STATUS;

        $id_user    = session('LOGGED_STORAGE');
        $id_yard    = session('IDYARD_STORAGE');


        //Cek apakah container sudah gate in
        $qcek_gati = "SELECT COUNT(NO_CONTAINER) AS JUM
			  FROM GATE_IN
			  WHERE NO_CONTAINER = '$no_cont'
			  AND NO_REQUEST = '$no_req'";
        $rwc_gati = DB::connection('uster')->selectOne($qcek_gati);
        $jum_gati = $rwc_gati->jum;
        if ($jum_gati > 0) {
            echo "EXIST_GATI";
            exit();
        }

        //Cek apakah container sudah di request receiving
        $query_rec = "SELECT COUNT(NO_CONTAINER) AS JUM
			  FROM CONTAINER_RECEIVING
			  WHERE NO_CONTAINER = '$no_cont'
			  AND NO_REQUEST = '$no_req'
				";
        $row_rec    =  DB::connection('uster')->selectOne($query_rec);
        $jum_req        = $row_rec->jum;

        //Cek apakah container sudah lunas
        $query_lunas = "SELECT CASE
					 WHEN PERALIHAN = 'STUFFING' THEN (SELECT DISTINCT LUNAS FROM NOTA_STUFFING INNER JOIN REQUEST_STUFFING ON NOTA_STUFFING.NO_REQUEST = REQUEST_STUFFING.NO_REQUEST WHERE REQUEST_STUFFING.NO_REQUEST_RECEIVING = '$no_req')
					 WHEN PERALIHAN = 'STRIPPING' THEN (SELECT DISTINCT LUNAS FROM NOTA_STRIPPING INNER JOIN REQUEST_STRIPPING ON NOTA_STRIPPING.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST WHERE REQUEST_STRIPPING.NO_REQUEST_RECEIVING = '$no_req')
					 WHEN PERALIHAN = 'RELOKASI' THEN 'YES'
					 WHEN PERALIHAN IS NULL THEN (SELECT DISTINCT LUNAS FROM NOTA_RECEIVING WHERE NO_REQUEST = '$no_req')
					 ELSE 'NO'
					 END AS LUNAS
				FROM REQUEST_RECEIVING WHERE NO_REQUEST = '$no_req'
				";

        $row_lunas    =  DB::connection('uster')->selectOne($query_lunas);
        $lunas        = $row_lunas->lunas;

        //Cek posisi container
        $query_gati = "SELECT LOCATION
			  FROM MASTER_CONTAINER
			  WHERE NO_CONTAINER = '$no_cont'
				";
        $row_gati    =  DB::connection('uster')->selectOne($query_gati);
        $gati        = $row_gati->location;

        //Cek asal receiving darimana, tpk atau luar
        $query_rec_dari = "SELECT RECEIVING_DARI, ID_YARD
				FROM REQUEST_RECEIVING
				WHERE NO_REQUEST = '$no_req'
			 ";

        $row_rec_dari = DB::connection('uster')->selectOne($query_rec_dari);
        $rec_dari = $row_rec_dari->receiving_dari;
        //$id_yard = 	$row_rec_dari["ID_YARD"];

        if ($rec_dari != "DEPO") {
            if ($jum_req <= 0) {
                echo "NOT_REQUEST";
            } else if ($lunas != "YES") {
                echo "NOT_PAID";
            } else if ($no_pol     == NULL) {
                echo "NO_POL";
            } else if ($jum_req >= 0 && $lunas == "YES" && $no_pol     != NULL && $gati != "GATI") {
                //Insert data cont ke tabel get in
                $query_insert    = "INSERT INTO GATE_IN(NO_CONTAINER, NO_REQUEST,NOPOL, ID_USER, TGL_IN,STATUS, ID_YARD) VALUES('$no_cont', '$no_req','$no_pol', '$id_user', SYSDATE, '$status','$id_yard')";
                $result_insert    = DB::connection('uster')->insert($query_insert);


                $qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE, STATUS_CONT FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND KEGIATAN = 'REQUEST RECEIVING'";
                $rwbook =  DB::connection('uster')->selectOne($qbook);
                $cur_booking1 = $rwbook->no_booking;
                $cur_counter1 = $rwbook->counter;
                $tgl_update = $rwbook->tgl_update;
                $status_ = $rwbook->status_cont;


                $history  = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, STATUS_CONT, NO_BOOKING, COUNTER) 
                                                      VALUES ('$no_cont','$no_req','GATE IN',SYSDATE,'$id_user','$id_yard','$status_','$cur_booking1','$cur_counter1')";

                DB::connection('uster')->insert($history);

                //Update status lokasi container, di dalam atau di luar
                $query_upd    = "UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$no_cont'";
                DB::connection('uster')->update($query_upd);

                //hist 
                $query = "INSERT INTO HIST_LOCATION(NO_CONTAINER, NO_REQUEST, LOCATION) VALUES('$no_cont','$no_req','GATI')";
                DB::connection('uster')->insert($query);

                //Select Nopol
                $query_nopol = "SELECT NO_TRUCK
						   FROM TRUCK
						   WHERE NO_TRUCK = '$no_pol'
							";
                if (DB::connection('uster')->selectOne($query_nopol)) {
                    $query_insert_nopol = "UPDATE GATE_IN SET TRUCKING ='PELINDO' WHERE NO_CONTAINER = '$no_cont'";
                    $result_insert_nopol    = DB::connection('uster')->update($query_insert_nopol);
                }
                echo "OK";
            } else if ($gati == "GATI") {
                echo "EXIST";
            }
        } else if ($rec_dari == "TPK") {
            if ($jum_req <= 0) {
                echo "NOT_REQUEST";
            } else if ($no_pol     == NULL) {
                echo "NO_POL";
            } else if ($jum_req >= 0  && $no_pol     != NULL && $gati != "GATI") {
                //Insert data cont ke tabel get in
                $query_insert    = "INSERT INTO GATE_IN(NO_CONTAINER, NO_REQUEST,NOPOL, ID_USER, TGL_IN) VALUES('$no_cont', '$no_req','$no_pol', '$id_user', SYSDATE)";
                $result_insert    =   DB::connection('uster')->insert($query_insert);;

                //Update status lokasi container, di dalam atau di luar
                $query_upd    = "UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$no_cont'";
                DB::connection('uster')->update($query_upd);

                //Insert ke handling piutang



                $kegiatan_ = array("LIFT_ON", "HAULAGE", "LIFT_OFF");
                foreach ($kegiatan_ as $kegiatan) {
                    $query_insert = "INSERT INTO HANDLING_PIUTANG
												(NO_CONTAINER,
												 KEGIATAN,
												 STATUS_CONT,
												 TANGGAL,
												 PENAGIHAN,
												 KETERANGAN
												)
										VALUES	('$no_cont',
												 '$kegiatan',
												 (SELECT STATUS 
													FROM CONTAINER_RECEIVING          
													WHERE NO_CONTAINER = '$no_cont'
													AND NO_REQUEST ='$no_req'  ),
												  SYSDATE,
												  'PELAYARAN',
												  'FAKTOR_YOR'
												)";
                    $result_query_insert =   DB::connection('uster')->insert($query_insert);
                }
                //Cek Truck Apakh dari pelindo atau tidak
                $query_nopol = "SELECT NO_TRUCK
							   FROM TRUCK
							   WHERE NO_TRUCK = '$no_pol'
							";
                if (DB::connection('uster')->selectOne($query_nopol)) {
                    $query_insert_nopol = "UPDATE GATE_IN SET TRUCKING ='PELINDO' WHERE NO_CONTAINER = '$no_cont'";
                    $result_insert_nopol    =   DB::connection('uster')->update($query_insert_nopol);
                }
                echo "OK";
            } else if ($gati == "GATI") {
                echo "EXIST";
            }
        }
    }
}
