<?php

namespace App\Services\Koreksi;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BatalStuffingService
{
    public function getCont($term)
    {
        $query             = "SELECT MASTER_CONTAINER.NO_CONTAINER,
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
                                WHERE MASTER_CONTAINER.NO_CONTAINER LIKE '%$term%'
                                AND CONTAINER_STUFFING.AKTIF = 'Y'";

        $data = DB::connection('uster')->select($query);
        return $data;
    }

    public function processBatal($request)
    {
        $no_cont        = $request->NO_CONT;
        $no_req_stuff    = $request->NO_REQ_STUFF;
        $stuffing_dari    = $request->ASAL_CONT;
        $stuffing_mode    = $request->STUFFING_DARI;
        $nm_user        = Session::get("NAME");
        $no_req_del        = $request->NO_REQ_DEL;
        $no_req_ict        = $request->NO_REQ_ICT;
        $hz             = $request->HZ;
        $keterangan        = $request->KETERANGAN;
        $no_seal        = $request->NO_SEAL;
        $berat            = $request->BERAT;
        $via            = $request->VIA;
        $komoditi       = $request->KOMODITI;
        $kd_komoditi    = $request->KD_KOMODITI;
        $size            = $request->SIZE;
        $tipe            = $request->TYPE;
        $status            = "MTY";
        $no_booking        = $request->NO_BOOKING;
        $no_ukk            = $request->NO_UKK;
        $id_user        = Session::get('LOGGED_STORAGE');

        $no_cont    = $request->NO_CONT;
        $no_req        = $request->NO_REQ;
        $no_req2    = substr($no_req, 3);
        $no_req2    = "UD" . $no_req2;

        $query_no_rec = "SELECT NO_REQUEST_RECEIVING
                            FROM PLAN_REQUEST_STUFFING
                            WHERE NO_REQUEST = REPLACE('$no_req_stuff','S','P')";
        $row_no_rec    = DB::connection('uster')->selectOne($query_no_rec);

        if(empty($row_no_rec)){
            throw new Exception('No. Request Receiving kosong', 500);
        }

        $no_req_rec    = $row_no_rec->no_request_receiving;
        $row_result2 = substr($no_req_rec, 3);
        $no_req_del_ict    = "UREC" . $row_result2;

        $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
        $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
        $cur_counter = $rw_getcounter->counter;
        $cur_booking = $rw_getcounter->no_booking;

        $cek_nota_stuf = "SELECT NO_NOTA FROM NOTA_STUFFING WHERE NO_REQUEST='$no_req_stuff'";
        $row_cek_nota_stuf = DB::connection('uster')->selectOne($cek_nota_stuf);
        $nota_stuf = $row_cek_nota_stuf->no_nota;

        if(empty($row_cek_nota_stuff)){
            throw new Exception('Nota Stuffing kosong', 500);
        }

        DB::beginTransaction();
        try {
            if ((!empty($row_cek_nota_stuf) && $nota_stuf != null) or $stuffing_mode == 'AUTO') {
                if ($stuffing_dari == "TPK") {
                    $update_batal_rec = "UPDATE CONTAINER_RECEIVING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'";
                    DB::connection('uster')->statement($update_batal_rec);

                    $update_batal_stuff = "UPDATE CONTAINER_STUFFING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_stuff' AND NO_CONTAINER='$no_cont'";
                    DB::connection('uster')->statement($update_batal_stuff);

                    $update_batal_plan_stuff = "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF='T' WHERE NO_REQUEST = REPLACE('$no_req_stuff','S','P') AND NO_CONTAINER='$no_cont'";
                    DB::connection('uster')->statement($update_batal_plan_stuff);

                    $query_insert_history = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER,NO_REQUEST,KEGIATAN,TGL_UPDATE,ID_USER, NO_BOOKING, COUNTER, STATUS_CONT )
                                                VALUES('$no_cont','$no_req','BATAL STUFFING',SYSDATE,'$id_user', '$cur_booking', '$cur_counter', '$status')";
                    DB::connection('uster')->statement($query_insert_history);
                } else {
                    $update_batal_rec = "UPDATE CONTAINER_RECEIVING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_rec' AND NO_CONTAINER='$no_cont'";
                    DB::connection('uster')->statement($update_batal_rec);

                    $update_batal_stuff = "UPDATE CONTAINER_STUFFING SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$no_req_stuff' AND NO_CONTAINER='$no_cont'";
                    DB::connection('uster')->statement($update_batal_stuff);

                    if (count($row_no_rec) > 0) {
                        $update_batal_plan_stuff = "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF='T' WHERE NO_REQUEST = REPLACE('$no_req_stuff','S','P') AND NO_CONTAINER='$no_cont'";
                        DB::connection('uster')->statement($update_batal_plan_stuff);
                    }

                    $query_insert_history = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER,NO_REQUEST,KEGIATAN,TGL_UPDATE,ID_USER, NO_BOOKING, COUNTER,STATUS_CONT)
                                                VALUES('$no_cont','$no_req_stuff','BATAL STUFFING',SYSDATE,'$id_user', '$cur_booking', '$cur_counter','$status')";
                    DB::connection('uster')->statement($query_insert_history);
                }

                DB::commit();
                return response()->json([
                    'status' => 'OK',
                    'msg' => 'Berhasil Batal Container Stripping',
                    'code' => 200
                ], 200);
            } else {
                throw new Exception('Nota Stuffing Kosong', 500);
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
            ]);
        }
    }
}
