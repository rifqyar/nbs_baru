<?php

namespace App\Services\Maintenance\CancelOperation;

use Exception;
use Illuminate\Support\Facades\DB;

class CancelOperationService
{
    function getNoContainer($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_delivery.no_container, container_delivery.no_request, history_container.no_booking
        FROM container_delivery, request_delivery, nota_delivery, history_container
       WHERE     request_delivery.no_request = nota_delivery.no_request
             AND request_delivery.no_request = container_delivery.no_request
             AND container_delivery.no_request = history_container.no_request
             AND container_delivery.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST DELIVERY'
             AND container_delivery.no_container IS NOT NULL
             AND nota_delivery.lunas = 'YES'
             AND nota_delivery.status <> 'BATAL'
             AND container_delivery.no_container LIKE '$nama%'             
             ORDER BY request_delivery.tgl_request desc";
        // echo $query;
        // die();
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getRequest($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_delivery.no_container, container_delivery.no_request, history_container.no_booking
        FROM container_delivery, request_delivery, nota_delivery, history_container
       WHERE     request_delivery.no_request = nota_delivery.no_request
             AND request_delivery.no_request = container_delivery.no_request
             AND container_delivery.no_request = history_container.no_request
             AND container_delivery.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST DELIVERY'
             AND container_delivery.no_container IS NOT NULL
             AND nota_delivery.lunas = 'YES'
             AND nota_delivery.status <> 'BATAL'             
             AND container_delivery.no_request LIKE '$nama%'
             ORDER BY request_delivery.tgl_request desc";
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getTableData($request)
    {
        $no_request = $request->NO_REQUEST;
        $no_container = $request->NO_CONT;
        $no_booking = $request->NO_BOOKING;
        $q_operation = "SELECT X.* FROM (SELECT MC.NO_CONTAINER, HC.KEGIATAN, 
                CASE  
                WHEN HC.KEGIATAN = 'BORDER GATE OUT'
                THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM BORDER_GATE_OUT WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RD.NO_REQUEST)
                WHEN HC.KEGIATAN = 'GATE OUT'
                THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM GATE_OUT WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RD.NO_REQUEST)
                WHEN HC.KEGIATAN = 'REQUEST DELIVERY'
                THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_DELIVERY WHERE NO_REQUEST = RD.NO_REQUEST)
                END TGL_UPDATE, HC.NO_BOOKING NO_BOOKING, MU.NAMA_LENGKAP, HC.NO_REQUEST
                FROM MASTER_CONTAINER MC INNER JOIN HISTORY_CONTAINER HC
                ON MC.NO_CONTAINER = HC.NO_CONTAINER 
                LEFT JOIN REQUEST_DELIVERY RD
                ON RD.NO_REQUEST = HC.NO_REQUEST
                LEFT JOIN MASTER_USER MU ON TO_CHAR(MU.ID) = HC.ID_USER
                WHERE HC.NO_CONTAINER = '$no_container'
                AND HC.NO_BOOKING = '$no_booking'
                AND HC.KEGIATAN IN ('BORDER GATE OUT', 'GATE OUT')) X
                ORDER BY X.TGL_UPDATE DESC";
        $result_query    = DB::connection('uster')->select($q_operation);
        return $result_query;
    }

    function deleteOperation($request)
    {
        DB::beginTransaction();
        try {
            $no_cont     = $request->NO_CONTAINER;
            $no_req     = $request->NO_REQUEST;
            $kegiatan     = $request->KEGIATAN;
            $no_booking    = $request->NO_BOOKING;
            $id_user        = session()->get("LOGGED_STORAGE");

            $q_cek_op     = "SELECT HC.*,
				         CASE
				            WHEN HC.NO_REQUEST LIKE '%REC%' THEN 'RECEIVING'
				            WHEN HC.NO_REQUEST LIKE '%STR%' THEN 'STRIPPING'
				            WHEN HC.NO_REQUEST LIKE '%STF%' THEN 'STUFFING'
				         END
				            CUR_OPERATION
				    FROM HISTORY_CONTAINER HC
				   WHERE HC.NO_CONTAINER = '$no_cont'
				ORDER BY HC.TGL_UPDATE DESC";

            $rw_cek_op    = DB::connection('uster')->selectOne($q_cek_op);

            $cek_tpk = "SELECT *
  				FROM request_delivery
 				WHERE delivery_ke = 'TPK' AND no_request = '$no_req'";
            $rwcek_tpk = DB::connection('uster')->select($cek_tpk);
            if (count($rwcek_tpk) > 0) {
                $cek_pl_tpk = "SELECT * FROM PETIKEMAS_CABANG.ttd_cont_exbspl WHERE kd_pmb = replace('$no_req','DEL','UD') AND no_container = '$no_cont' and status_pmb_dtl in ('1U','1')";
                $rwcek_pl_tpk = DB::connection('uster')->select($cek_pl_tpk);
                if (count($rwcek_pl_tpk) > 0) {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'PL',
                        ],
                    ];
                    die();
                }
            }



            if ($kegiatan == $rw_cek_op->kegiatan) {
                if ($kegiatan == 'GATE OUT') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("DELETE FROM GATE_OUT WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("UPDATE CONTAINER_DELIVERY SET AKTIF = 'Y', KELUAR = 'N' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'IN_YARD' WHERE NO_CONTAINER = '$no_cont'");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                } else if ($kegiatan == 'BORDER GATE OUT') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("DELETE FROM BORDER_GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'IN_YARD' WHERE NO_CONTAINER = '$no_cont'");
                    DB::connection('uster')->statement("UPDATE PETIKEMAS_CABANG.ttd_cont_exbspl SET status_pmb_dtl = '0U'  WHERE no_container = '$no_cont' AND kd_pmb = replace('$no_req','DEL','UD')");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                }
                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Y',
                    ],
                ];
                die();
            } else {
                if ($rw_cek_op->cur_operation == 'RECEIVING') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'RC',
                        ],
                    ];
                    die();
                } else if ($rw_cek_op->cur_operation == 'STRIPPING' || $rw_cek_op->cur_operation == 'STUFFING') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'ST',
                        ],
                    ];
                    die();
                }

                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Success',
                    ],
                ];
            }
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

    function getNoContainerReceiving($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_receiving.no_container, container_receiving.no_request, history_container.no_booking
        FROM container_receiving, request_receiving, nota_receiving, history_container
       WHERE     request_receiving.no_request = nota_receiving.no_request
             AND request_receiving.no_request = container_receiving.no_request
             AND container_receiving.no_request = history_container.no_request
             AND container_receiving.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST RECEIVING'
             AND container_receiving.no_container IS NOT NULL
             AND nota_receiving.lunas = 'YES'
             AND nota_receiving.status <> 'BATAL'
             AND container_receiving.no_container LIKE '$nama%'
             --AND container_receiving.no_request = :q
             ORDER BY request_receiving.tgl_request desc";
        // echo $query;
        // die();
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getRequestReceiving($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_receiving.no_container, container_receiving.no_request, history_container.no_booking
        FROM container_receiving, request_receiving, nota_receiving, history_container
       WHERE     request_receiving.no_request = nota_receiving.no_request
             AND request_receiving.no_request = container_receiving.no_request
             AND container_receiving.no_request = history_container.no_request
             AND container_receiving.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST RECEIVING'
             AND container_receiving.no_container IS NOT NULL
             AND nota_receiving.lunas = 'YES'
             AND nota_receiving.status <> 'BATAL'
             --AND container_receiving.no_container = :q
             AND container_receiving.no_request LIKE '$nama%'
             ORDER BY request_receiving.tgl_request desc";
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getTableDataReceiving($request)
    {
        $no_request = $request->NO_REQUEST;
        $no_container = $request->NO_CONT;
        $no_booking = $request->NO_BOOKING;
        $q_operation = "SELECT X.* FROM (SELECT MC.NO_CONTAINER, HC.KEGIATAN, 
                CASE  WHEN HC.KEGIATAN = 'GATE IN'
                THEN (SELECT to_date(to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi'),'DD-MM-YYYY hh24:mi') FROM GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RR.NO_REQUEST)
                END TGL_UPDATE1,
                CASE  WHEN HC.KEGIATAN = 'GATE IN'
                THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RR.NO_REQUEST)
                END TGL_UPDATE, HC.NO_BOOKING NO_BOOKING, MU.NAMA_LENGKAP, HC.NO_REQUEST
                FROM MASTER_CONTAINER MC INNER JOIN HISTORY_CONTAINER HC
                ON MC.NO_CONTAINER = HC.NO_CONTAINER 
                LEFT JOIN REQUEST_RECEIVING RR
                ON RR.NO_REQUEST = HC.NO_REQUEST
                LEFT JOIN MASTER_USER MU ON MU.ID = HC.ID_USER
                WHERE HC.NO_CONTAINER = '$no_container'
                AND HC.NO_BOOKING = '$no_booking'
                AND HC.NO_REQUEST = '$no_request'
                AND HC.KEGIATAN IN ('GATE IN')
            UNION ALL   
            SELECT HP.NO_CONTAINER, 'PLACEMENT' KEGIATAN,
                to_date(TO_CHAR(HP.TGL_UPDATE,'DD-MM-YYYY hh24:mi'),'DD-MM-YYYY hh24:mi') TGL_UPDATE1, 
                TO_CHAR(HP.TGL_UPDATE,'DD-MM-YYYY hh24:mi') TGL_UPDATE, HC.NO_BOOKING, MU.NAMA_LENGKAP, HP.NO_REQUEST
                FROM HISTORY_PLACEMENT HP, HISTORY_CONTAINER HC, MASTER_USER MU
                WHERE HP.NO_CONTAINER = HC.NO_CONTAINER
                AND HP.NO_REQUEST = HC.NO_REQUEST
                AND HC.KEGIATAN = 'GATE IN'
                AND MU.USERNAME = HP.NIPP_USER
                AND HP.NO_CONTAINER = '$no_container'
                AND HC.NO_REQUEST = '$no_request'
                AND HC.NO_BOOKING = '$no_booking'
                UNION ALL
                SELECT HP.NO_CONTAINER, 'PLACEMENT' KEGIATAN,
                to_date(TO_CHAR(HP.TGL_UPDATE,'DD-MM-YYYY hh24:mi'),'DD-MM-YYYY hh24:mi') TGL_UPDATE1,
                TO_CHAR(HP.TGL_UPDATE,'DD-MM-YYYY hh24:mi') TGL_UPDATE, HC.NO_BOOKING, MU.NAMA_LENGKAP, HP.NO_REQUEST
                FROM HISTORY_PLACEMENT HP, HISTORY_CONTAINER HC, MASTER_USER MU
                WHERE HP.NO_CONTAINER = HC.NO_CONTAINER
                AND HP.NO_REQUEST = HC.NO_REQUEST
                AND HC.KEGIATAN = 'GATE IN'
                AND MU.NIPP(+) = HP.NIPP_USER
                AND HP.NO_CONTAINER = '$no_container'
                AND HC.NO_REQUEST = '$no_request'
                AND HC.NO_BOOKING = '$no_booking') X
                ORDER BY X.TGL_UPDATE1 DESC";
        $result_query    = DB::connection('uster')->select($q_operation);
        return $result_query;
    }

    function deleteOperationReceiving($request)
    {
        DB::beginTransaction();
        try {
            $no_cont     = $request->NO_CONTAINER;
            $no_req     = $request->NO_REQUEST;
            $kegiatan     = $request->KEGIATAN;
            $no_booking    = $request->NO_BOOKING;
            $id_user        = session()->get("LOGGED_STORAGE");

            $q_cek_op     = "SELECT HC.*, CASE WHEN HC.NO_REQUEST LIKE '%DEL%' THEN 'DELIVERY'
                                WHEN HC.NO_REQUEST LIKE '%TF%' THEN 'STUFFING' END CUR_OPERATION FROM HISTORY_CONTAINER HC WHERE HC.NO_CONTAINER = '$no_cont' ORDER BY HC.TGL_UPDATE DESC";

            $rw_cek_op    = DB::connection('uster')->selectOne($q_cek_op);


            if ($kegiatan == $rw_cek_op->kegiatan) {
                if ($kegiatan == 'REALISASI STRIPPING') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("UPDATE CONTAINER_STRIPPING SET AKTIF = 'Y', TGL_REALISASI = '', ID_USER_REALISASI = '' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                } else if ($kegiatan == 'GATE IN') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("DELETE FROM GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$no_cont'");
                    //$db2->query("UPDATE PETIKEMAS_CABANG.TTD_BP_CONT SET STATUS_CONT = '04',GATE_OUT = '', GATE_OUT_DATE = '', HT_NO = '' WHERE CONT_NO_BP = '$no_cont' AND BP_ID ='$no_booking'");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                }
                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Y',
                    ],
                ];
                die();
            } else if ($kegiatan == 'PLACEMENT') {
                DB::connection('uster')->statement("DELETE FROM HISTORY_PLACEMENT WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                DB::connection('uster')->statement("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont'");
                DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$no_cont'");
                DB::connection('uster')->statement("UPDATE CONTAINER_RECEIVING SET AKTIF = 'Y' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Y',
                    ],
                ];
                die();
            } else {
                if ($rw_cek_op->cur_operation == 'DELIVERY') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'RC',
                        ],
                    ];
                    die();
                } else if ($rw_cek_op->cur_operation == 'STUFFING') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'ST',
                        ],
                    ];
                    die();
                }

                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Success',
                    ],
                ];
            }
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
    function getNoContainerStripping($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_stripping.no_container, container_stripping.no_request, history_container.no_booking
        FROM container_stripping, request_stripping, nota_stripping, history_container
       WHERE     request_stripping.no_request = nota_stripping.no_request
             AND request_stripping.no_request = container_stripping.no_request
             AND container_stripping.no_request = history_container.no_request
             AND container_stripping.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST STRIPPING'
             AND container_stripping.no_container IS NOT NULL
             AND nota_stripping.lunas = 'YES'
             AND nota_stripping.status <> 'BATAL'
             AND container_stripping.no_container LIKE '%$nama%'
             --AND container_stripping.no_request = :q
             ORDER BY request_stripping.tgl_request desc";
        // echo $query;
        // die();
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getRequestStripping($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_stripping.no_container, container_stripping.no_request, history_container.no_booking
        FROM container_stripping, request_stripping, nota_stripping, history_container
       WHERE     request_stripping.no_request = nota_stripping.no_request
             AND request_stripping.no_request = container_stripping.no_request
             AND container_stripping.no_request = history_container.no_request
             AND container_stripping.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST STRIPPING'
             AND container_stripping.no_container IS NOT NULL
             AND nota_stripping.lunas = 'YES'
             AND nota_stripping.status <> 'BATAL'           
           AND container_stripping.no_request LIKE '%$nama%'
           ORDER BY request_stripping.tgl_request desc";
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getTableDataStripping($request)
    {
        $no_request = $request->NO_REQUEST;
        $no_container = $request->NO_CONT;
        $no_booking = $request->NO_BOOKING;
        $q_operation = "SELECT X.* FROM (SELECT MC.NO_CONTAINER, HC.KEGIATAN, 
                            CASE WHEN HC.KEGIATAN = 'REALISASI STRIPPING'
                            THEN (SELECT to_char(MAX(TGL_REALISASI),'DD-MM-YYYY hh24:mi')  FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RST.NO_REQUEST) 
                            WHEN HC.KEGIATAN = 'BORDER GATE IN'
                            THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM BORDER_GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RR.NO_REQUEST)
                            WHEN HC.KEGIATAN = 'REQUEST STRIPPING'
                            THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_STRIPPING WHERE NO_REQUEST = RST.NO_REQUEST)
                            END TGL_UPDATE, HC.NO_BOOKING NO_BOOKING, NVL(MU.NAMA_LENGKAP,HC.ID_USER) NAMA_LENGKAP, HC.NO_REQUEST
                            FROM MASTER_CONTAINER MC INNER JOIN HISTORY_CONTAINER HC
                            ON MC.NO_CONTAINER = HC.NO_CONTAINER 
                            LEFT JOIN REQUEST_RECEIVING RR
                            ON RR.NO_REQUEST = HC.NO_REQUEST
                            LEFT JOIN REQUEST_STRIPPING RST
                            ON RST.NO_REQUEST = HC.NO_REQUEST
                            LEFT JOIN MASTER_USER MU ON to_char(MU.ID) = to_char(HC.ID_USER)
                            WHERE HC.NO_CONTAINER = '$no_container'
                            AND HC.NO_BOOKING = '$no_booking'
                            AND HC.KEGIATAN IN ('BORDER GATE IN','REALISASI STRIPPING')
                        UNION ALL   
                        SELECT HP.NO_CONTAINER, 'PLACEMENT' KEGIATAN,
                            TO_CHAR(HP.TGL_UPDATE,'DD-MM-YYYY hh24:mi') TGL_UPDATE, HC.NO_BOOKING, MU.NAMA_LENGKAP, HP.NO_REQUEST
                            FROM HISTORY_PLACEMENT HP, HISTORY_CONTAINER HC, MASTER_USER MU
                            WHERE HP.NO_CONTAINER = HC.NO_CONTAINER
                            AND HP.NO_REQUEST = HC.NO_REQUEST
                            AND HC.KEGIATAN = 'BORDER GATE IN'
                            AND MU.USERNAME = HP.NIPP_USER
                            AND HP.NO_CONTAINER = '$no_container'
                            AND HC.NO_BOOKING = '$no_booking') X
                            ORDER BY X.TGL_UPDATE DESC";
        $result_query    = DB::connection('uster')->select($q_operation);
        return $result_query;
    }

    function deleteOperationStripping($request)
    {
        DB::beginTransaction();
        try {
            $no_cont     = $request->NO_CONTAINER;
            $no_req     = $request->NO_REQUEST;
            $kegiatan     = $request->KEGIATAN;
            $no_booking    = $request->NO_BOOKING;
            $id_user        = session()->get("LOGGED_STORAGE");

            $q_cek_op     = "SELECT HC.*, CASE WHEN HC.NO_REQUEST LIKE '%DEL%' THEN 'DELIVERY'
                                WHEN HC.NO_REQUEST LIKE '%TF%' THEN 'STUFFING' END CUR_OPERATION FROM HISTORY_CONTAINER HC WHERE HC.NO_CONTAINER = '$no_cont' ORDER BY HC.TGL_UPDATE DESC";

            $rw_cek_op    = DB::connection('uster')->selectOne($q_cek_op);


            if ($kegiatan == $rw_cek_op->kegiatan) {
                if ($kegiatan == 'REALISASI STRIPPING') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("UPDATE CONTAINER_STRIPPING SET AKTIF = 'Y', TGL_REALISASI = '', ID_USER_REALISASI = '' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                } else if ($kegiatan == 'BORDER GATE IN') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("DELETE FROM BORDER_GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$no_cont'");
                    DB::connection('uster')->statement("UPDATE PETIKEMAS_CABANG.TTD_BP_CONT SET STATUS_CONT = '04',GATE_OUT = '', GATE_OUT_DATE = '', HT_NO = '' WHERE CONT_NO_BP = '$no_cont' AND BP_ID ='$no_booking'");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                }
                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Y',
                    ],
                ];
                die();
            } else if ($kegiatan == 'PLACEMENT') {
                DB::connection('uster')->statement("DELETE FROM HISTORY_PLACEMENT WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                DB::connection('uster')->statement("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont'");
                DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$no_cont'");
                DB::connection('uster')->statement("UPDATE CONTAINER_RECEIVING SET AKTIF = 'Y' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Y',
                    ],
                ];
                die();
            } else {
                if ($rw_cek_op->cur_operation == 'DELIVERY') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'RC',
                        ],
                    ];
                    die();
                } else if ($rw_cek_op->cur_operation == 'STUFFING') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'ST',
                        ],
                    ];
                    die();
                }

                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Success',
                    ],
                ];
            }
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
    function getNoContainerStuffing($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_stuffing.no_container, container_stuffing.no_request, history_container.no_booking
        FROM container_stuffing, request_stuffing, nota_stuffing, history_container
       WHERE     request_stuffing.no_request = nota_stuffing.no_request
             AND request_stuffing.no_request = container_stuffing.no_request
             AND container_stuffing.no_request = history_container.no_request
             AND container_stuffing.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST STUFFING'
             AND container_stuffing.no_container IS NOT NULL
             AND nota_stuffing.lunas = 'YES'
             AND nota_stuffing.status <> 'BATAL'
             AND container_stuffing.no_container LIKE '$nama%'
             --AND container_stuffing.no_request = :q
             ORDER BY request_stuffing.tgl_request desc";
        // echo $query;
        // die();
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getRequestStuffing($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT container_stuffing.no_container, container_stuffing.no_request, history_container.no_booking
        FROM container_stuffing, request_stuffing, nota_stuffing, history_container
       WHERE     request_stuffing.no_request = nota_stuffing.no_request
             AND request_stuffing.no_request = container_stuffing.no_request
             AND container_stuffing.no_request = history_container.no_request
             AND container_stuffing.no_container = history_container.no_container
             AND history_container.kegiatan = 'REQUEST STUFFING'
             AND container_stuffing.no_container IS NOT NULL
             AND nota_stuffing.lunas = 'YES'
             AND nota_stuffing.status <> 'BATAL'             
             AND container_stuffing.no_request LIKE '$nama%'
             ORDER BY request_stuffing.tgl_request desc";
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function getTableDataStuffing($request)
    {
        $no_request = $request->NO_REQUEST;
        $no_container = $request->NO_CONT;
        $no_booking = $request->NO_BOOKING;
        $q_operation = "SELECT X.* FROM (SELECT MC.NO_CONTAINER, HC.KEGIATAN, 
        CASE WHEN HC.KEGIATAN = 'REALISASI STUFFING'
        THEN (SELECT to_char(MAX(TGL_REALISASI),'DD-MM-YYYY hh24:mi')  FROM CONTAINER_STUFFING WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RST.NO_REQUEST) 
        WHEN HC.KEGIATAN = 'BORDER GATE IN'
        THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM BORDER_GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RR.NO_REQUEST)
        WHEN HC.KEGIATAN = 'GATE IN'
        THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RR.NO_REQUEST)
        WHEN HC.KEGIATAN = 'REQUEST STUFFING'
        THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_STUFFING WHERE NO_REQUEST = RST.NO_REQUEST)
        END TGL_UPDATE, HC.NO_BOOKING NO_BOOKING, MU.NAMA_LENGKAP, HC.NO_REQUEST
        FROM MASTER_CONTAINER MC INNER JOIN HISTORY_CONTAINER HC
        ON MC.NO_CONTAINER = HC.NO_CONTAINER 
        LEFT JOIN REQUEST_RECEIVING RR
        ON RR.NO_REQUEST = HC.NO_REQUEST
        LEFT JOIN REQUEST_STUFFING RST
        ON RST.NO_REQUEST = HC.NO_REQUEST
        LEFT JOIN MASTER_USER MU ON MU.ID = HC.ID_USER
        WHERE HC.NO_CONTAINER = '$no_container'
        AND HC.NO_BOOKING = '$no_booking'
        AND HC.KEGIATAN IN ('BORDER GATE IN','REALISASI STUFFING', 'GATE IN')
       UNION ALL   
    SELECT HP.NO_CONTAINER, 'PLACEMENT' KEGIATAN,
        TO_CHAR(HP.TGL_UPDATE,'DD-MM-YYYY hh24:mi') TGL_UPDATE, HC.NO_BOOKING, MU.NAMA_LENGKAP, HP.NO_REQUEST
        FROM HISTORY_PLACEMENT HP, HISTORY_CONTAINER HC, MASTER_USER MU
        WHERE HP.NO_CONTAINER = HC.NO_CONTAINER
        AND HP.NO_REQUEST = HC.NO_REQUEST
        AND HC.KEGIATAN = 'BORDER GATE IN'
        AND MU.USERNAME = HP.NIPP_USER
        AND HP.NO_CONTAINER = '$no_container'
        AND HC.NO_BOOKING = '$no_booking'
        UNION ALL
        SELECT HP.NO_CONTAINER, 'PLACEMENT' KEGIATAN,
        TO_CHAR(HP.TGL_UPDATE,'DD-MM-YYYY hh24:mi') TGL_UPDATE, HC.NO_BOOKING, MU.NAMA_LENGKAP, HP.NO_REQUEST
        FROM HISTORY_PLACEMENT HP, HISTORY_CONTAINER HC, MASTER_USER MU
        WHERE HP.NO_CONTAINER = HC.NO_CONTAINER
        AND HP.NO_REQUEST = HC.NO_REQUEST
        AND HC.KEGIATAN = 'BORDER GATE IN'
        AND TO_CHAR(MU.ID) = HP.NIPP_USER
        AND HP.NO_CONTAINER = '$no_container'
        AND HC.NO_BOOKING = '$no_booking'   
        ) X
        ORDER BY X.TGL_UPDATE DESC";
        $result_query    = DB::connection('uster')->select($q_operation);
        return $result_query;
    }

    function deleteOperationStuffing($request)
    {
        DB::beginTransaction();
        try {
            $no_cont     = $request->NO_CONTAINER;
            $no_req     = $request->NO_REQUEST;
            $kegiatan     = $request->KEGIATAN;
            $no_booking    = $request->NO_BOOKING;
            $id_user        = session()->get("LOGGED_STORAGE");

            $q_cek_op     = "SELECT HC.*, CASE WHEN HC.NO_REQUEST LIKE '%DEL%' THEN 'DELIVERY'
            WHEN HC.NO_REQUEST LIKE '%TF%' THEN 'STUFFING' END CUR_OPERATION FROM HISTORY_CONTAINER HC WHERE HC.NO_CONTAINER = '$no_cont' ORDER BY HC.TGL_UPDATE DESC";

            $rw_cek_op    = DB::connection('uster')->selectOne($q_cek_op);


            if ($kegiatan == $rw_cek_op->kegiatan) {
                if ($kegiatan == 'REALISASI STUFFING') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("UPDATE CONTAINER_STUFFING SET AKTIF = 'Y', TGL_REALISASI = '', ID_USER_REALISASI = '' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                } else if ($kegiatan == 'BORDER GATE IN') {
                    DB::connection('uster')->statement("DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req' AND NO_BOOKING = '$no_booking' AND KEGIATAN = '$kegiatan'");
                    DB::connection('uster')->statement("DELETE FROM BORDER_GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                    DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$no_cont'");
                    DB::connection('uster')->statement("UPDATE PETIKEMAS_CABANG.TTD_BP_CONT SET STATUS_CONT = '04',GATE_OUT = '', GATE_OUT_DATE = '', HT_NO = '' WHERE CONT_NO_BP = '$no_cont' AND BP_ID ='$no_booking'");
                    DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                }
                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Y',
                    ],
                ];
                die();
            } else if ($kegiatan == 'PLACEMENT') {
                DB::connection('uster')->statement("DELETE FROM HISTORY_PLACEMENT WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                DB::connection('uster')->statement("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont'");
                DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$no_cont'");
                DB::connection('uster')->statement("UPDATE CONTAINER_RECEIVING SET AKTIF = 'Y' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'");
                DB::connection('uster')->statement("INSERT INTO CANCEL_OPERATION_LOG(NO_CONTAINER,NO_REQUEST,KEGIATAN,USERS,TIMES) VALUES('$no_cont','$no_req','$kegiatan','$id_user',SYSDATE)");
                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Y',
                    ],
                ];
                die();
            } else {
                if ($rw_cek_op->cur_operation == 'DELIVERY') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'RC',
                        ],
                    ];
                    die();
                } else if ($rw_cek_op->cur_operation == 'STUFFING') {
                    DB::commit();
                    return [
                        'status' => [
                            'code' => 200,
                            'msg' => 'ST',
                        ],
                    ];
                    die();
                }

                DB::commit();
                return [
                    'status' => [
                        'code' => 200,
                        'msg' => 'Success',
                    ],
                ];
            }
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
