<?php

namespace App\Services\Print;

use Exception;
use Illuminate\Support\Facades\DB;

class KartuStripping
{

    function GetSSPStripping($request)
    {

        $no_request = $request->search['value'] ?? NULL;
        $from = $request->from;
        $to = $request->to;


        if ($no_request != NULL && $from == NULL && $to == NULL) {
            $query_list    = "SELECT DISTINCT REQUEST_STRIPPING.*, 
                                NVL(REQUEST_STRIPPING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                pnmt.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STRIPPING.LUNAS,'NO') AS LUNAS
                            FROM REQUEST_STRIPPING INNER JOIN V_MST_PBM emkl ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM 
                                JOIN V_MST_PBM pnmt ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = pnmt.KD_PBM
                               LEFT OUTER  JOIN NOTA_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                            WHERE 
							--REQUEST_STRIPPING.TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE) 
								--AND 
								REQUEST_STRIPPING.NO_REQUEST = '$no_request'
                            ORDER BY REQUEST_STRIPPING.NO_REQUEST DESC";
        } else if ($no_request == NULL && $from != NULL && $to != NULL) {
            $query_list    = "SELECT DISTINCT REQUEST_STRIPPING.*, 
                                NVL(REQUEST_STRIPPING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                pnmt.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STRIPPING.LUNAS,'NO') AS LUNAS
                            FROM REQUEST_STRIPPING INNER JOIN V_MST_PBM emkl ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM 
                                JOIN V_MST_PBM pnmt ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = pnmt.KD_PBM
                               LEFT OUTER  JOIN NOTA_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                            WHERE REQUEST_STRIPPING.TGL_REQUEST BETWEEN TO_DATE('$from', 'yy-mm-dd') AND TO_DATE('$to','yy-mm-dd') 								
                            ORDER BY REQUEST_STRIPPING.NO_REQUEST DESC";
        } else if ($no_request == NULL && $from == NULL && $to == NULL) {
            $query_list    = " SELECT DISTINCT
					         REQUEST_STRIPPING.*,
					         NVL (REQUEST_STRIPPING.NO_REQUEST_RECEIVING, ' - ') AS REQ_REC,
					         emkl.NM_PBM AS NAMA_CONSIGNEE,
					         emkl.NM_PBM AS NAMA_PENUMPUK,
					         NVL (NOTA_STRIPPING.LUNAS, 'NO') AS LUNAS
					    FROM REQUEST_STRIPPING
					         INNER JOIN V_MST_PBM emkl
					            ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
					               AND emkl.KD_CABANG = '05'
					         LEFT OUTER JOIN NOTA_STRIPPING
					            ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
					   WHERE REQUEST_STRIPPING.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAY
					                                           AND LAST_DAY (SYSDATE)
					ORDER BY REQUEST_STRIPPING.TGL_REQUEST DESC";
        } else {
            $query_list    = "SELECT DISTINCT REQUEST_STRIPPING.*, 
                                NVL(REQUEST_STRIPPING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                pnmt.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STRIPPING.LUNAS,'NO') AS LUNAS
                            FROM REQUEST_STRIPPING INNER JOIN V_MST_PBM emkl ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM 
                                JOIN V_MST_PBM pnmt ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = pnmt.KD_PBM
                               LEFT OUTER  JOIN NOTA_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                            WHERE REQUEST_STRIPPING.TGL_REQUEST BETWEEN TO_DATE('$from', 'yy-mm-dd') AND TO_DATE('$to','yy-mm-dd')
								AND REQUEST_STRIPPING.NO_REQUEST = '$no_request'
                            ORDER BY REQUEST_STRIPPING.NO_REQUEST DESC";
        }

        return DB::connection('uster')->select($query_list);
    }


    function GetTruckStripping($request)
    {

        $no_request = $request->search['value'] ?? NULL;
        $from = $request->from;
        $to = $request->to;

        if ($no_request != NULL && $from == NULL && $to == NULL) {
            $query_list = "SELECT 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM AS NAMA_CONSIGNEE,
                                    emkl.NM_PBM AS NAMA_PENUMPUK,
                                    NVL (NOTA_STRIPPING.LUNAS, 'NO') AS LUNAS
                                FROM REQUEST_STRIPPING
                                 INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
                                 LEFT  JOIN NOTA_STRIPPING
                                    ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                                 INNER JOIN CONTAINER_STRIPPING cs 
                                     ON cs.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST 
                                     AND cs.NO_CONTAINER NOT LIKE '%rename%'
                                   WHERE REQUEST_STRIPPING.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAY AND LAST_DAY (SYSDATE)
                                       AND REQUEST_STRIPPING.NO_REQUEST = '$no_request'
                                   GROUP BY 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM,
                                    emkl.NM_PBM,
                                    NOTA_STRIPPING.LUNAS
                                ORDER BY REQUEST_STRIPPING.TGL_REQUEST DESC";
        }
        if ($no_request == NULL && $from == NULL && $to == NULL) {
            $query_list = "SELECT 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM AS NAMA_CONSIGNEE,
                                    emkl.NM_PBM AS NAMA_PENUMPUK,
                                    NVL (NOTA_STRIPPING.LUNAS, 'NO') AS LUNAS
                                FROM REQUEST_STRIPPING
                                 INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
                                 LEFT  JOIN NOTA_STRIPPING
                                    ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                                 INNER JOIN CONTAINER_STRIPPING cs 
                                     ON cs.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST 
                                     AND cs.NO_CONTAINER NOT LIKE '%rename%'
                                   WHERE REQUEST_STRIPPING.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAY AND LAST_DAY (SYSDATE)
                                   GROUP BY 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM,
                                    emkl.NM_PBM,
                                    NOTA_STRIPPING.LUNAS
                                ORDER BY REQUEST_STRIPPING.TGL_REQUEST DESC";
        } else if ($no_request == NULL && $from != NULL && $to != NULL) {
            $query_list = "SELECT 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM AS NAMA_CONSIGNEE,
                                    emkl.NM_PBM AS NAMA_PENUMPUK,
                                    NVL (NOTA_STRIPPING.LUNAS, 'NO') AS LUNAS
                                FROM REQUEST_STRIPPING
                                 INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
                                 LEFT  JOIN NOTA_STRIPPING
                                    ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                                 INNER JOIN CONTAINER_STRIPPING cs 
                                     ON cs.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST 
                                     AND cs.NO_CONTAINER NOT LIKE '%rename%'
                                   WHERE REQUEST_STRIPPING.TGL_REQUEST BETWEEN TO_DATE('$from', 'yy-mm-dd') AND TO_DATE('$to','yy-mm-dd')
                                   GROUP BY 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM,
                                    emkl.NM_PBM,
                                    NOTA_STRIPPING.LUNAS
                                ORDER BY REQUEST_STRIPPING.TGL_REQUEST DESC";
        } else {
            $query_list = "SELECT 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM AS NAMA_CONSIGNEE,
                                    emkl.NM_PBM AS NAMA_PENUMPUK,
                                    NVL (NOTA_STRIPPING.LUNAS, 'NO') AS LUNAS
                                FROM REQUEST_STRIPPING
                                 INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
                                 LEFT  JOIN NOTA_STRIPPING
                                    ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                                 INNER JOIN CONTAINER_STRIPPING cs 
                                     ON cs.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST 
                                     AND cs.NO_CONTAINER NOT LIKE '%rename%'
                                   WHERE REQUEST_STRIPPING.TGL_REQUEST BETWEEN TO_DATE('$from', 'yy-mm-dd') AND TO_DATE('$to','yy-mm-dd')
                                       AND REQUEST_STRIPPING.NO_REQUEST = '$no_request'
                                   GROUP BY 
                                    REQUEST_STRIPPING.NO_REQUEST,
                                    REQUEST_STRIPPING.TGL_REQUEST,
                                    emkl.NM_PBM,
                                    emkl.NM_PBM,
                                    NOTA_STRIPPING.LUNAS
                                ORDER BY REQUEST_STRIPPING.TGL_REQUEST DESC";
        }


        return DB::connection('uster')->select($query_list);
    }

    function Cetak($no_request){
       

    }

    function CetakSPK($no_request){
      
    }

    function Cetakan($no_request){
       
    }
}
