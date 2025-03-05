<?php

namespace App\Services\Koreksi;

use Exception;
use Illuminate\Support\Facades\DB;

class BatalReceivingService
{
    function getNoContainer($no_cont)
    {

        $no_cont = strtoupper($no_cont);
        $query             = "SELECT cr.no_container, mc.type_, mc.size_, rr.receiving_dari, nr.no_nota, rr.no_request, rr.tgl_request, nr.lunas
        from master_container mc
        join container_receiving cr on mc.no_container = cr.no_container
        join request_receiving rr on cr.no_request = rr.no_request
        join nota_receiving nr on rr.no_request = nr.no_request
        and rr.receiving_dari = 'LUAR' AND cr.aktif = 'Y'
        and mc.location = 'GATO' and nr.status <> 'BATAL' and cr.STATUS_REQ is null
        and cr.no_container like '%$no_cont%'";
        $result_query    = DB::connection('uster')->select($query);

        return $result_query;
    }

    function batalReceivingCont($request)
    {
        DB::beginTransaction();
        try {
            $no_cont        = $request->NO_CONT;
            $no_req            = $request->NO_REQ;
            $id_user        = session()->get("LOGGED_STORAGE");
            $id_yard         = session()->get("YARD_STORAGE");

            $cek_gati = "SELECT * FROM GATE_IN WHERE NO_CONTAINER='$no_cont' AND NO_REQUEST = '$no_req'";
            $rw_gate = DB::connection('uster')->selectOne($cek_gati);

            if ($rw_gate->no_container != NULL) {
                throw new Exception('Container Sudah Gate In', 400);
            }



            $q_getcounter = "SELECT NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
            $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
            $cur_counter = $rw_getcounter->counter;
            $cur_booking = $rw_getcounter->no_booking;
            $location = $rw_getcounter->location;

            if ($location == 'GATO') {
                $rbatal = "UPDATE CONTAINER_RECEIVING SET AKTIF = 'T', STATUS_REQ = 'BATAL' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'";
                if (DB::connection('uster')->statement($rbatal)) {
                    $qhist = "UPDATE HISTORY_CONTAINER SET AKTIF = 'T' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND KEGIATAN = 'REQUEST RECEIVING'";
                    if (DB::connection('uster')->statement($qhist)) {
                        $lasthist = "SELECT * FROM HISTORY_CONTAINER WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont' AND KEGIATAN = 'REQUEST RECEIVING'";
                        $rwl = DB::connection('uster')->selectOne($lasthist);
                        $no_booking = $rwl->no_booking;
                        $counter = $rwl->counter;
                        $status_cont = $rwl->status_cont;
                        $ihist = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER,NO_REQUEST,KEGIATAN,TGL_UPDATE,ID_USER,ID_YARD,STATUS_CONT,NO_BOOKING,COUNTER,AKTIF)
			                        VALUES('$no_cont','$no_req','BATAL RECEIVING',SYSDATE,'$id_user','46','$status_cont','$no_booking','$counter','T')";

                        DB::connection('uster')->statement($ihist);
                    }
                }
            } else {
                throw new Exception('Container Masih IN YARD', 400);
                exit();
            }

            DB::commit();
            return [
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
            ];
        } catch (Exception $th) {
            DB::rollBack();
            return [
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ];
        }
    }
}
