<?php

namespace App\Services\Print;

use Exception;
use Illuminate\Support\Facades\DB;

class KartuRepo
{
    function GetCardRepo($request)
    {
        $no_request = $request->search['value'] ?? NULL;
        $from = $request->from;
        $to = $request->to;
        $start = $request->start;
        $length = $request->length;

        if ((isset($no_request)) && ($from == NULL) && ($to == NULL)) {
            $query_list        = "SELECT NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, REQUEST_DELIVERY.NO_REQUEST, 
                                TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd Mon yyyy') TGL_REQUEST, 
                                TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, 
                                emkl.NM_PBM as NAMA_EMKL ,request_delivery.VESSEL as NAMA_VESSEL, 
                                request_delivery.VOYAGE, yard_area.NAMA_YARD, request_delivery.NO_REQ_ICT,
                                REQUEST_DELIVERY.JN_REPO
                                FROM REQUEST_DELIVERY, NOTA_DELIVERY, v_mst_pbm emkl, yard_area
                                WHERE  REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                                AND emkl.KD_CABANG = '05'
                                AND REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                AND NOTA_DELIVERY.NO_REQUEST(+) = REQUEST_DELIVERY.NO_REQUEST
                                AND request_delivery.DELIVERY_KE = 'TPK'
                                AND request_delivery.NO_REQUEST = '$no_request'
                                --AND NOTA_DELIVERY.STATUS <> 'BATAL' 
                                AND request_delivery.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING') 
                                ORDER BY REQUEST_DELIVERY.TGL_REQUEST DESC";
        } else if ((isset($from)) && (isset($to)) && ($no_request == NULL)) {
            $query_list        = " SELECT NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, REQUEST_DELIVERY.NO_REQUEST, 
                                TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd Mon yyyy') TGL_REQUEST, 
                                TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, 
                                emkl.NM_PBM as NAMA_EMKL ,request_delivery.VESSEL as NAMA_VESSEL, 
                                request_delivery.VOYAGE, yard_area.NAMA_YARD, request_delivery.NO_REQ_ICT,
                                REQUEST_DELIVERY.JN_REPO
                                FROM REQUEST_DELIVERY, NOTA_DELIVERY, v_mst_pbm emkl, yard_area
                                WHERE  REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                                AND emkl.KD_CABANG = '05'
                                AND REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                AND NOTA_DELIVERY.NO_REQUEST(+) = REQUEST_DELIVERY.NO_REQUEST
                                AND request_delivery.DELIVERY_KE = 'TPK'
                                AND request_delivery.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING') 
                                AND request_delivery.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                    AND TO_DATE (CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                ORDER BY REQUEST_DELIVERY.TGL_REQUEST DESC";
        } else if ((isset($from)) && (isset($to)) && (isset($no_request))) {
            $query_list        = " SELECT NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, REQUEST_DELIVERY.NO_REQUEST, 
                                TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd Mon yyyy') TGL_REQUEST, 
                                TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, 
                                emkl.NM_PBM as NAMA_EMKL ,request_delivery.VESSEL as NAMA_VESSEL, 
                                request_delivery.VOYAGE, yard_area.NAMA_YARD, request_delivery.NO_REQ_ICT,
                                REQUEST_DELIVERY.JN_REPO
                                FROM REQUEST_DELIVERY, NOTA_DELIVERY, v_mst_pbm emkl, yard_area
                                WHERE  REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                                AND emkl.KD_CABANG = '05'
                                AND REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                AND NOTA_DELIVERY.NO_REQUEST(+) = REQUEST_DELIVERY.NO_REQUEST
                                AND request_delivery.DELIVERY_KE = 'TPK'
                                AND request_delivery.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING') 
                                AND request_delivery.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                    AND TO_DATE (CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                AND request_delivery.NO_REQUEST = '$no_request'
                                ORDER BY REQUEST_DELIVERY.TGL_REQUEST DESC
                                   ";
        } else {
            $limit = ($length*3)+$start;
            $query_list        = "SELECT * FROM (
                SELECT NVL (NOTA_DELIVERY.LUNAS, 0) LUNAS,
                       REQUEST_DELIVERY.NO_REQUEST,
                       TO_CHAR (REQUEST_DELIVERY.TGL_REQUEST, 'dd Mon yyyy') TGL_REQUEST,
                       TO_CHAR (REQUEST_DELIVERY.TGL_REQUEST_DELIVERY, 'dd/mm/yyyy')
                          TGL_REQUEST_DELIVERY,
                       emkl.NM_PBM AS NAMA_EMKL,
                       request_delivery.VESSEL AS NAMA_VESSEL,
                       request_delivery.VOYAGE,
                       request_delivery.NO_REQ_ICT,
                       REQUEST_DELIVERY.JN_REPO
                  FROM REQUEST_DELIVERY,
                       NOTA_DELIVERY,
                       v_mst_pbm emkl
                 WHERE     REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                       AND emkl.KD_CABANG = '05'
                       AND NOTA_DELIVERY.NO_REQUEST(+) = REQUEST_DELIVERY.NO_REQUEST
                       AND request_delivery.DELIVERY_KE = 'TPK'
                       AND request_delivery.PERALIHAN NOT IN
                              ('RELOKASI', 'STUFFING', 'STRIPPING')
              ORDER BY REQUEST_DELIVERY.TGL_REQUEST DESC)
              WHERE ROWNUM <= $limit";
        }
        
        return DB::connection('uster')->select($query_list);
    }

    function GetAction($no_request)
    {
        $query        = "SELECT * FROM NOTA_DELIVERY WHERE NO_REQUEST = '$no_request'";
        $row        = DB::connection('uster')->select($query);


        $query_req1    = "SELECT * FROM REQUEST_DELIVERY WHERE NO_REQUEST = '$no_request' ";
        $row_req1    = DB::connection('uster')->selectOne($query_req1);

        $row_req1_lunas = $row_req1->lunas?? null;
        $button = '';

        if ($row_req1->delivery_ke == "TPK") {
            if (count($row) > 0) {
                $query_nota        = "SELECT * FROM NOTA_DELIVERY WHERE NO_REQUEST = '$no_request' ORDER BY NO_NOTA DESC";
                $row_nota    = DB::connection('uster')->selectOne($query_nota);

                $query_req        = "SELECT * FROM REQUEST_DELIVERY WHERE NO_REQUEST = '$no_request' ";
                $row_req    = DB::connection('uster')->selectOne($query_req);
                
                $row_nota_lunas = $row_nota->lunas?? null;

                if ($row_nota_lunas == "YES" && $row_req->cetak_kartu == 0) {
                    $no_req    = $row_nota->no_request;
                    $url = route('uster.print.GetPrayaNota').'?no_req='.$no_req;
                    $button .=  '<a class="btn btn-primary" href="' . $url . '" target="_blank"> CETAK KARTU </a> | ';
                    $petik = "'";
                    $button .=  '<a class="btn btn-primary" onclick="print_by_container(' . $petik . '' . $no_req . '' . $petik . ')"> Cetak Per Container </a>';
                } else if ($row_req->cetak_kartu > 0 && $row_nota_lunas == "YES") {
                    $no_req    = $row_nota->no_request;
                    $url = route('uster.print.GetPrayaNota').'?no_req='.$no_req;
                    $button .=  '<a class="btn btn-primary" href="' . $url . '" target="_blank"> CETAK ULANG </a> | ';
                    $petik = "'";
                    $button .=  '<a class="btn btn-primary" onclick="print_by_container(' . $petik . '' . $no_req . '' . $petik . ')"> Cetak Per Container </a>';
                } else {
                    $button .= " BELUM LUNAS";
                }
            } else if ($row_req1_lunas == 'AUTO_REPO') {
                $rbmu  = DB::connection('uster')->selectOne("SELECT BIAYA FROM REQUEST_BATAL_MUAT WHERE NO_REQ_BARU = '$no_request'");
                if ($rbmu["BIAYA"] == "Y") {
                    if ($row_req1->nota == 'Y') {
                        $no_req    = $row_req1->no_request;;
                        $button .=  '<a class="btn btn-primary" href="/print/print_receiving_card?no_req=' . $no_req . '" target="_blank"> CETAK KARTU </a> | ';
                        $petik = "'";
                        $button .=  '<a class="btn btn-primary" onclick="print_by_container(' . $petik . '' . $no_req . '' . $petik . ')"> Cetak Per Container </a>';
                    } else {
                        $button .= ' BELUM BAYAR BATAL MUAT ';
                    }
                } else {
                    $no_req    = $row_req1->no_request;
                    $button .=  '<a class="btn btn-primary" href="/print/print_receiving_card?no_req=' . $no_req . '" target="_blank"> CETAK KARTU </a> | ';
                    $petik = "'";
                    $button .=  '<a class="btn btn-primary" onclick="print_by_container(' . $petik . '' . $no_req . '' . $petik . ')"> Cetak Per Container </a>';
                }
            } else {
                $button .= ' <span" class="btn btn-info">BELUM LUNAS</span>';
            }
        }
        return $button;
    }
}
