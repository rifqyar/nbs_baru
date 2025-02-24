<?php

namespace App\Services\Print;

use Exception;
use Illuminate\Support\Facades\DB;

class KartuStuffing
{
    function GetSSPStuffing($request)
    {

        $no_request = $request->search['value'] ?? NULL;
        $from = $request->from;
        $to = $request->to;


        if ((isset($no_request)) && ($from == NULL) && ($to == NULL)) {
            $query_list        = "SELECT REQUEST_STUFFING.NO_REQUEST,
							         REQUEST_STUFFING.TGL_REQUEST,
							         NVL (REQUEST_STUFFING.NO_REQUEST_RECEIVING, ' - ') AS REQ_REC,
							         emkl.NM_PBM AS NAMA_CONSIGNEE,
                                     emkl.NM_PBM AS NAMA_PENUMPUK,
							         NVL (NOTA_STUFFING.LUNAS, 'NO') AS LUNAS,
							         COUNT (container_stuffing.NO_CONTAINER) JML
							    FROM REQUEST_STUFFING
							         INNER JOIN v_mst_pbm emkl
							            ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM
							               AND emkl.KD_CABANG = '05'
							         LEFT JOIN NOTA_STUFFING
							            ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
							         INNER JOIN container_stuffing
							            ON container_stuffing.NO_REQUEST = request_stuffing.no_request
							   WHERE REQUEST_STUFFING.NO_REQUEST LIKE '%$no_request%'
							         AND REQUEST_STUFFING.STUFFING_DARI NOT IN ('AUTO')
							GROUP BY REQUEST_STUFFING.NO_REQUEST,
							         REQUEST_STUFFING.TGL_REQUEST,
							         NVL (REQUEST_STUFFING.NO_REQUEST_RECEIVING, ' - '),
							         emkl.NM_PBM,
							         NVL (NOTA_STUFFING.LUNAS, 'NO')
							ORDER BY REQUEST_STUFFING.TGL_REQUEST DESC";
        } else if ((isset($from)) && (isset($to)) && ($no_request == NULL)) {
            $query_list        = " SELECT REQUEST_STUFFING.NO_REQUEST, REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                emkl.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STUFFING.LUNAS,'NO') AS LUNAS,
                                COUNT(container_stuffing.NO_CONTAINER) JML
                            FROM REQUEST_STUFFING 
                            INNER JOIN V_MST_PBM emkl ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM 
                               LEFT OUTER  JOIN NOTA_STUFFING ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
                            INNER JOIN container_stuffing ON container_stuffing.NO_REQUEST = request_stuffing.no_request
                            WHERE request_stuffing.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                GROUP BY REQUEST_STUFFING.NO_REQUEST,
								REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') , 
                                emkl.NM_PBM,
                                NVL(NOTA_STUFFING.LUNAS,'NO')
                            ORDER BY REQUEST_STUFFING.NO_REQUEST DESC";
        } else if ((isset($from)) && (isset($to)) && (isset($no_request))) {
            $query_list        = " SELECT REQUEST_STUFFING.NO_REQUEST, REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                emkl.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STUFFING.LUNAS,'NO') AS LUNAS,
                                COUNT(container_stuffing.NO_CONTAINER) JML
                            FROM REQUEST_STUFFING 
                            INNER JOIN V_MST_PBM emkl ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM 
                               LEFT OUTER  JOIN NOTA_STUFFING ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
                            INNER JOIN container_stuffing ON container_stuffing.NO_REQUEST = request_stuffing.no_request
                            WHERE request_stuffing.no_request = '$no_request'
							AND request_stuffing.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                GROUP BY REQUEST_STUFFING.NO_REQUEST,
								REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') , 
                                emkl.NM_PBM,
                                NVL(NOTA_STUFFING.LUNAS,'NO')
                            ORDER BY REQUEST_STUFFING.NO_REQUEST DESC";
        } else {
            $query_list = "SELECT REQUEST_STUFFING.NO_REQUEST,
				         REQUEST_STUFFING.TGL_REQUEST,
				         NVL (REQUEST_STUFFING.NO_REQUEST_RECEIVING, ' - ') AS REQ_REC,
				         emkl.NM_PBM AS NAMA_CONSIGNEE,
                           emkl.NM_PBM AS NAMA_PENUMPUK,
				         NVL (NOTA_STUFFING.LUNAS, 'NO') AS LUNAS,
				         COUNT (container_stuffing.NO_CONTAINER) JML
				    FROM REQUEST_STUFFING
				         INNER JOIN v_mst_pbm emkl
				            ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM
				               AND emkl.KD_CABANG = '05'
				         LEFT JOIN NOTA_STUFFING
				            ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
				         INNER JOIN container_stuffing
				            ON container_stuffing.NO_REQUEST = request_stuffing.no_request
				   WHERE REQUEST_STUFFING.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAY
				                                          AND LAST_DAY (SYSDATE)
				         AND REQUEST_STUFFING.STUFFING_DARI NOT IN ('AUTO')
				GROUP BY REQUEST_STUFFING.NO_REQUEST,
				         REQUEST_STUFFING.TGL_REQUEST,
				         NVL (REQUEST_STUFFING.NO_REQUEST_RECEIVING, ' - '),
				         emkl.NM_PBM,
				         NVL (NOTA_STUFFING.LUNAS, 'NO')
				ORDER BY REQUEST_STUFFING.TGL_REQUEST DESC";
        }

        return DB::connection('uster')->select($query_list);
    }


    function GetTruckStuffing($request)
    {
        $no_request = $request->search['value'] ?? NULL;
        $from = $request->from;
        $to = $request->to;
        if ($no_request != NULL && $from == NULL && $to == NULL) {
            $query_list        = " SELECT REQUEST_STUFFING.NO_REQUEST, REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                emkl.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STUFFING.LUNAS,'NO') AS LUNAS,
                                COUNT(container_stuffing.NO_CONTAINER) JML
                            FROM REQUEST_STUFFING 
                            INNER JOIN V_MST_PBM emkl ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM 
                               LEFT OUTER  JOIN NOTA_STUFFING ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
                            INNER JOIN container_stuffing 
                            	ON container_stuffing.NO_REQUEST = request_stuffing.no_request
                            	AND container_stuffing.NO_CONTAINER NOT LIKE '%rename%'
                            WHERE request_stuffing.no_request = '$no_request'
							and REQUEST_STUFFING.TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') 
                                AND LAST_DAY(SYSDATE) 
                                GROUP BY REQUEST_STUFFING.NO_REQUEST,
								REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') , 
                                emkl.NM_PBM,
                                NVL(NOTA_STUFFING.LUNAS,'NO')
                            ORDER BY REQUEST_STUFFING.NO_REQUEST DESC";
        } else if ($no_request == NULL && $from != NULL && $to != NULL) {
            $query_list        = " SELECT REQUEST_STUFFING.NO_REQUEST, REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                emkl.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STUFFING.LUNAS,'NO') AS LUNAS,
                                COUNT(container_stuffing.NO_CONTAINER) JML
                            FROM REQUEST_STUFFING 
                            INNER JOIN V_MST_PBM emkl ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM 
                               LEFT OUTER  JOIN NOTA_STUFFING ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
                            INNER JOIN container_stuffing 
                            	ON container_stuffing.NO_REQUEST = request_stuffing.no_request
                            	AND container_stuffing.NO_CONTAINER NOT LIKE '%rename%'
                            WHERE request_stuffing.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'yy-mm-dd ')
                                AND  TO_DATE ( '$to', 'yy-mm-dd ')
                                GROUP BY REQUEST_STUFFING.NO_REQUEST,
								REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') , 
                                emkl.NM_PBM,
                                NVL(NOTA_STUFFING.LUNAS,'NO')
                            ORDER BY REQUEST_STUFFING.NO_REQUEST DESC";
        } else if ($no_request == NULL && $from == NULL && $to == NULL) {
            $query_list = " SELECT REQUEST_STUFFING.NO_REQUEST, REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                emkl.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STUFFING.LUNAS,'NO') AS LUNAS,
                                COUNT(container_stuffing.NO_CONTAINER) JML
                            FROM REQUEST_STUFFING 
                            INNER JOIN V_MST_PBM emkl ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM 
                            LEFT OUTER  JOIN NOTA_STUFFING ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
                            INNER JOIN container_stuffing 
                            	ON container_stuffing.NO_REQUEST = request_stuffing.no_request
                            	AND container_stuffing.NO_CONTAINER NOT LIKE '%rename%'
                            WHERE REQUEST_STUFFING.TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') 
                                AND LAST_DAY(SYSDATE) 
                                GROUP BY REQUEST_STUFFING.NO_REQUEST,
								REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') , 
                                emkl.NM_PBM,
                                NVL(NOTA_STUFFING.LUNAS,'NO')
                            ORDER BY REQUEST_STUFFING.NO_REQUEST DESC";
        } else {
            $query_list        = " SELECT REQUEST_STUFFING.NO_REQUEST, REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') AS REQ_REC, 
                                emkl.NM_PBM AS NAMA_CONSIGNEE,
                                emkl.NM_PBM AS NAMA_PENUMPUK,
                                NVL(NOTA_STUFFING.LUNAS,'NO') AS LUNAS,
                                COUNT(container_stuffing.NO_CONTAINER) JML
                            FROM REQUEST_STUFFING 
                            INNER JOIN V_MST_PBM emkl ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM 
                               LEFT OUTER  JOIN NOTA_STUFFING ON REQUEST_STUFFING.NO_REQUEST = NOTA_STUFFING.NO_REQUEST
                            INNER JOIN container_stuffing 
                            	ON container_stuffing.NO_REQUEST = request_stuffing.no_request
                            	AND container_stuffing.NO_CONTAINER NOT LIKE '%rename%'
                            WHERE request_stuffing.no_request = '$no_request'
							AND request_stuffing.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                GROUP BY REQUEST_STUFFING.NO_REQUEST,
								REQUEST_STUFFING.TGL_REQUEST, 
                                NVL(REQUEST_STUFFING.NO_REQUEST_RECEIVING,' - ') , 
                                emkl.NM_PBM,
                                NVL(NOTA_STUFFING.LUNAS,'NO')
                            ORDER BY REQUEST_STUFFING.NO_REQUEST DESC";
        }
        return DB::connection('uster')->select($query_list);
    }
}
