<?php

namespace App\Services\Koreksi;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BatalStrippingService
{
    public function getCont($term)
    {
        $query             = "SELECT MASTER_CONTAINER.NO_CONTAINER,
                                        MASTER_CONTAINER.SIZE_ AS SIZE_,
                                        MASTER_CONTAINER.TYPE_ AS TYPE_,
                                        CONTAINER_STRIPPING.VIA AS VIA,
                                        CONTAINER_STRIPPING.TGL_BONGKAR AS TGL_REQUEST,
                                        CONTAINER_STRIPPING.NO_REQUEST AS NO_REQUEST,
                                        REQUEST_STRIPPING.TGL_REQUEST AS TGL_REQUEST,
                                        NOTA_STRIPPING.LUNAS AS LUNAS,
                                        'TPK' AS STRIPPING_DARI,
                                        REQUEST_STRIPPING.NO_REQUEST_RECEIVING AS NO_REQUEST_RECEIVING
                                FROM MASTER_CONTAINER
                                INNER JOIN CONTAINER_STRIPPING
                                    ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_STRIPPING.NO_CONTAINER
                                JOIN REQUEST_STRIPPING
                                    ON CONTAINER_STRIPPING.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST
                                JOIN NOTA_STRIPPING
                                    ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                                    AND NOTA_STRIPPING.LUNAS = 'YES'
                                    AND NOTA_STRIPPING.STATUS <> 'BATAL'
                                WHERE MASTER_CONTAINER.NO_CONTAINER LIKE '%$term%'
                                    AND CONTAINER_STRIPPING.AKTIF = 'Y'";

        $data = DB::connection('uster')->select($query);
        return $data;
    }

    public function processBatal($request)
    {
        $no_cont        = $request->NO_CONT;
        $no_req            = $request->NO_REQ;
        $stripping_dari    = $request->STRIPPING_DARI;
        $no_req_rec        = $request->NO_REQUEST_RECEIVING;
        $id_user        = Session::get('LOGGED_STORAGE');

        $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
        $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
        $cur_counter = $rw_getcounter->counter;
        $cur_booking = $rw_getcounter->no_booking;

        DB::beginTransaction();
        try {
            if ($stripping_dari == 'TPK') {
                $update_batal_rec = "UPDATE CONTAINER_RECEIVING SET STATUS_REQ='BATAL' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'";
                $execUpdateBatalRec = DB::connection('uster')->statement($update_batal_rec);

                $update_batal_strip = "UPDATE CONTAINER_STRIPPING SET STATUS_REQ='BATAL' WHERE NO_REQUEST ='$no_req' AND NO_CONTAINER='$no_cont'";
                $execUpdateBatalStrip = DB::connection('uster')->statement($update_batal_strip);

                $update_batal_plan_strip = "UPDATE PLAN_CONTAINER_STRIPPING SET AKTIF='T' WHERE NO_REQUEST = REPLACE('$no_req','S','P') AND NO_CONTAINER='$no_cont'";
                $execUpdateBatalPlanStrip = DB::connection('uster')->statement($update_batal_plan_strip);

                DB::connection('uster')->statement("UPDATE CONTAINER_STRIPPING SET AKTIF='T' WHERE NO_REQUEST ='$no_req' AND NO_CONTAINER='$no_cont'");
                DB::connection('uster')->statement("UPDATE CONTAINER_RECEIVING SET AKTIF='T' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'");

                $query_insert_history = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER,NO_REQUEST,KEGIATAN,TGL_UPDATE,ID_USER, NO_BOOKING, COUNTER, STATUS_CONT)
                                            VALUES('$no_cont','$no_req','BATAL STRIPPING',SYSDATE,'$id_user', '$cur_booking', '$cur_counter', 'FCL')";
                $execQueryInsertHistory = DB::connection('uster')->statement($query_insert_history);

                $q_ifperp = "SELECT * FROM REQUEST_STRIPPING WHERE NO_REQUEST = '$no_req'";
                $r_ifperp = DB::connection('uster')->selectOne($q_ifperp);
                if ($r_ifperp->status_req == 'PERP') {
                    $no_req_awal = $r_ifperp->perp_dari;
                    $update_batal_strip_ = "UPDATE CONTAINER_STRIPPING SET STATUS_REQ='BATAL' WHERE NO_REQUEST ='$no_req_awal' AND NO_CONTAINER='$no_cont'";
                    $execUpdateBatalStrip = DB::connection('uster')->statement($update_batal_strip_);
                }

                //update gato
                $q_gato = "UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$no_cont'";
                $execUpdateGato = DB::connection('uster')->statement($q_gato);

                DB::commit();
                return response()->json([
                    'status' => 'OK',
                    'msg' => 'Berhasil Batal Container Stripping',
                    'code' => 200
                ], 200);
            } else if ($stripping_dari == 'DEPO') {
                $update_batal_rec = "UPDATE CONTAINER_RECEIVING SET STATUS_REQ='BATAL' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'";
                $execUpdateBatalRec = DB::connection('uster')->statement($update_batal_rec);

                $update_batal_strip = "UPDATE CONTAINER_STRIPPING SET STATUS_REQ='BATAL' WHERE NO_REQUEST ='$no_req' AND NO_CONTAINER='$no_cont'";
                $execUpdateBatalStrip = DB::connection('uster')->statement($update_batal_strip);

                $update_batal_plan_strip = "UPDATE PLAN_CONTAINER_STRIPPING SET AKTIF='T' WHERE NO_REQUEST = REPLACE('$no_req','S','P') AND NO_CONTAINER='$no_cont'";
                $execUpdateBatalPlanStrip = DB::connection('uster')->statement($update_batal_plan_strip);

                DB::connection('uster')->statement("UPDATE CONTAINER_STRIPPING SET AKTIF='T' WHERE NO_REQUEST ='$no_req' AND NO_CONTAINER='$no_cont'");
                DB::connection('uster')->statement("UPDATE CONTAINER_RECEIVING SET AKTIF='T' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'");

                $query_insert_history = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER,NO_REQUEST,KEGIATAN,TGL_UPDATE,ID_USER, NO_BOOKING, COUNTER, STATUS_CONT)
                                            VALUES('$no_cont','$no_req','BATAL STRIPPING',SYSDATE,'$id_user','$cur_booking', '$cur_counter', 'FCL')";
                $execInsertHistory = DB::connection('uster')->statement($query_insert_history);

                DB::commit();
                return response()->json([
                    'status' => 'OK',
                    'msg' => 'Berhasil Batal Container Stripping',
                    'code' => 200
                ], 200);
            }
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'Error',
                'msg' => 'Gagal Melakukan Batal Container Stripping',
                'detail' => $th,
                'code' => 500,
            ], 500);
        }
    }
}
