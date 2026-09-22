<?php

namespace App\Services\Request\BatalStuffing;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class BatalStuffingService
{
   function getContainer($noCont)
   {
      $query = "SELECT MASTER_CONTAINER.NO_CONTAINER, 
                          NVL(PLAN_CONTAINER_STUFFING.ASAL_CONT,'DEPO') ASAL_CONT,
                          CONTAINER_STUFFING.NO_REQUEST AS NO_REQ_STUFF,
                          CONTAINER_STUFFING.TYPE_STUFFING AS VIA,
                          CONTAINER_STUFFING.COMMODITY AS KOMODITI,
                          CONTAINER_STUFFING.HZ AS HZ,
                          MASTER_CONTAINER.SIZE_ AS SIZE_, 
                          MASTER_CONTAINER.TYPE_ AS TYPE_,
                          REQUEST_STUFFING.TGL_REQUEST AS TGL_REQUEST,
						  REQUEST_STUFFING.STUFFING_DARI AS STUFFING_DARI,
                          CONTAINER_STUFFING.NO_SEAL AS NO_SEAL,
                          CONTAINER_STUFFING.BERAT AS BERAT,
                          CONTAINER_STUFFING.KETERANGAN AS KETERANGAN                        
                   FROM MASTER_CONTAINER 
                   INNER JOIN CONTAINER_STUFFING 
                        ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_STUFFING.NO_CONTAINER
                   JOIN REQUEST_STUFFING 
                        ON CONTAINER_STUFFING.NO_REQUEST = REQUEST_STUFFING.NO_REQUEST
                    LEFT JOIN PLAN_CONTAINER_STUFFING ON PLAN_CONTAINER_STUFFING.NO_CONTAINER = CONTAINER_STUFFING.NO_CONTAINER 
                        AND CONTAINER_STUFFING.NO_REQUEST = REPLACE(PLAN_CONTAINER_STUFFING.NO_REQUEST,'P','S')
                   LEFT JOIN NOTA_STUFFING ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
                   JOIN MST_PELANGGAN EMKL
                        ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                   WHERE MASTER_CONTAINER.NO_CONTAINER LIKE '%$noCont%'   
                   AND CONTAINER_STUFFING.AKTIF = 'Y'";
      $data = DB::connection('uster')->select($query);
      return $data;
   }

   function insertReq(Request $request)
   {
      DB::beginTransaction();
      try {
         $no_cont       = trim($request->no_cont);
         $no_req_stuff  = $request->id_req; // Dari frontend: NO_REQ_STUFF
         $stuffing_dari = $request->asal_cont;
         $stuffing_mode = $request->stuffing_mode ?? 'MANUAL'; 
         $id_user       = Session::get("LOGGED_STORAGE");
         $status        = "MTY";


         // $cek_history = "SELECT 1 FROM HISTORY_CONTAINER 
         //                 WHERE NO_CONTAINER = '$no_cont' 
         //                 AND NO_REQUEST = '$no_req_stuff' 
         //                 AND KEGIATAN = 'BATAL STUFFING'";
                         
         // $is_batal = DB::connection('uster')->selectOne($cek_history);
         // if ($is_batal) {
         //    throw new Exception("Container $no_cont dengan No Request $no_req_stuff sudah dibatalkan sebelumnya!", 400);
         // }

         // 1. Tembak ke Praya dulu
         $batal = batalContainer($no_cont, $no_req_stuff, 'batal stuffing');
         $status_praya = $batal->getData()->code ?? 500;
         if ($status_praya != 200) {
            throw new Exception('Gagal melakukan pembatalan container di praya', 500);
         }

         // 2. Ambil NO_REQUEST_RECEIVING dari PLAN_REQUEST_STUFFING
         $query_no_rec = "SELECT NO_REQUEST_RECEIVING
                          FROM PLAN_REQUEST_STUFFING
                          WHERE NO_REQUEST = REPLACE('$no_req_stuff','S','P')";
         $row_no_rec = DB::connection('uster')->selectOne($query_no_rec);
         $no_req_rec = $row_no_rec ? $row_no_rec->no_request_receiving : null;

         // 3. Ambil data MASTER_CONTAINER
         $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
         $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
         $cur_counter = $rw_getcounter ? $rw_getcounter->counter : null;
         $cur_booking = $rw_getcounter ? $rw_getcounter->no_booking : null;

         // 4. Cek Nota Stuffing
         $cek_nota_stuf = "SELECT NO_NOTA FROM NOTA_STUFFING WHERE NO_REQUEST='$no_req_stuff'";
         $row_cek_nota_stuf = DB::connection('uster')->selectOne($cek_nota_stuf);
         $nota_stuf = $row_cek_nota_stuf ? $row_cek_nota_stuf->no_nota : null;

         // 5. Proses Batal berdasarkan asal dan mode
         if ($nota_stuf != '' || $stuffing_mode == 'AUTO') {

            if ($stuffing_dari == "TPK") {
               // Update batal receiving
               $update_batal_rec = "UPDATE CONTAINER_RECEIVING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'";
               DB::connection('uster')->update($update_batal_rec);
               
               // Update batal stuffing
               $update_batal_stuff = "UPDATE CONTAINER_STUFFING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_stuff' AND NO_CONTAINER='$no_cont'";
               DB::connection('uster')->update($update_batal_stuff);
               
               // Update batal plan
               $update_batal_plan_stuff = "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF='T' WHERE NO_REQUEST = REPLACE('$no_req_stuff','S','P') AND NO_CONTAINER='$no_cont'";
               DB::connection('uster')->update($update_batal_plan_stuff);
               
               // History
               $query_insert_history = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, NO_BOOKING, COUNTER, STATUS_CONT)
                                        VALUES('$no_cont', '$no_req_stuff', 'BATAL STUFFING', SYSDATE, '$id_user', '$cur_booking', '$cur_counter', '$status')";
               DB::connection('uster')->insert($query_insert_history);

            } else { // Jika BUKAN TPK
               // Update batal receiving
               $update_batal_rec = "UPDATE CONTAINER_RECEIVING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'";
               DB::connection('uster')->update($update_batal_rec);
               
               // Update batal stuffing
               $update_batal_stuff = "UPDATE CONTAINER_STUFFING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_stuff' AND NO_CONTAINER='$no_cont'";
               DB::connection('uster')->update($update_batal_stuff);
               
               // Update batal plan
               if ($row_no_rec) {
                  $update_batal_plan_stuff = "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF='T' WHERE NO_REQUEST = REPLACE('$no_req_stuff','S','P') AND NO_CONTAINER='$no_cont'";
                  DB::connection('uster')->update($update_batal_plan_stuff);
               }
               
               // History
               $query_insert_history = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, NO_BOOKING, COUNTER, STATUS_CONT)
                                        VALUES('$no_cont', '$no_req_stuff', 'BATAL STUFFING', SYSDATE, '$id_user', '$cur_booking', '$cur_counter', '$status')";
               DB::connection('uster')->insert($query_insert_history);
            }

            DB::connection('uster')->commit();
            return response()->json([
               'status' => [
                  'code' => 200,
                  'msg' => 'OK'
               ]
            ], 200);

         } else {
            throw new Exception('Nota belum dicetak atau Container belum Lunas', 400);
         }
        
      } catch (Exception $th) {
         DB::rollBack();
         return response()->json([
            'status' => [
               'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
               'code' => $th->getCode() != '' ? $th->getCode() : 500,
            ],
            'data' => null,
            'err_detail' => $th,
            'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
         ], $th->getCode() != '' ? $th->getCode() : 500);
      }
   }
}
