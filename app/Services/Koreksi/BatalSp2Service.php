<?php

namespace App\Services\Koreksi;

use Exception;
use Illuminate\Support\Facades\DB;

class BatalSp2Service
{
    function getNoContainer($no_cont)
    {

        $no_cont = strtoupper($no_cont);
        $query             = "SELECT MASTER_CONTAINER.NO_CONTAINER, 
                          MASTER_CONTAINER.SIZE_ AS SIZE_, 
                          MASTER_CONTAINER.TYPE_ AS TYPE_,
                          container_delivery.VIA AS VIA,
                          container_delivery.START_STACK AS TGL_REQUEST,
                          container_delivery.NO_REQUEST AS NO_REQUEST,
                          request_delivery.TGL_REQUEST AS TGL_REQUEST,
                          nota_delivery.LUNAS AS LUNAS                        
                   FROM MASTER_CONTAINER  
                   INNER JOIN container_delivery  
                        ON MASTER_CONTAINER.NO_CONTAINER = container_delivery.NO_CONTAINER 
                   JOIN request_delivery 
                        ON container_delivery.NO_REQUEST = request_delivery.NO_REQUEST
                  JOIN nota_delivery
                        ON request_delivery.NO_REQUEST = nota_delivery.NO_REQUEST
                   WHERE MASTER_CONTAINER.NO_CONTAINER LIKE '$no_cont%'
                        AND container_delivery.AKTIF = 'Y'
						AND master_container.location not like 'GATO'
						and request_delivery.delivery_ke = 'LUAR'
						and nota_delivery.LUNAS = 'YES'
						and nota_delivery.STATUS <> 'BATAL'";
        $result_query    = DB::connection('uster')->select($query);

        return $result_query;
    }

    function batalSp2Cont($request)
    {

        DB::beginTransaction();
        try {
            $no_cont        = $request->NO_CONT;
            $no_req            = $request->NO_REQ;
            $keterangan        = $request->KETERANGAN;
            $id_user        = session()->get("LOGGED_STORAGE");
            $id_yard        = session()->get("IDYARD_STORAGE");

            $del = "SELECT * FROM CONTAINER_DELIVERY WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'";
            $rwl = DB::connection('uster')->selectOne($del);
            $bp_id = $rwl->ex_bp_id;
            $status = $rwl->status;
            //==============================================================Interface Ke ICT=============================================================================//
            //==============================================================================================================================================================================//


            //==============================================================End Of Interface Ke ICT======================================================================//
            //==============================================================================================================================================================================//

            $nonaktiv = "UPDATE CONTAINER_DELIVERY SET AKTIF = 'T', REMARK_BATAL = 'Y' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' ";
            DB::connection('uster')->statement($nonaktiv);

            $g_book = "SELECT NO_BOOKING, COUNTER FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'";
            $r_book = DB::connection('uster')->selectOne($g_book);
            $cur_book = $r_book->no_booking;
            $cur_count = $r_book->counter;

            $history = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, STATUS_CONT, NO_BOOKING, COUNTER, WHY) VALUES ('$no_cont','$no_req','BATAL SP2', SYSDATE, '$id_user','$id_yard','$status','$cur_book','$cur_count','$keterangan')";

            DB::connection('uster')->statement($history);

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
